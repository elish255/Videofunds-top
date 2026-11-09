<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once '../firebase_config.php';
require_once __DIR__ . '/../account_guard.php';

$uid   = $_SESSION['user_id'];
$token = $_SESSION['id_token'];

function firestore_request($url, $method = 'GET', $token = null, $data = null) {
    $headers = ['User-Agent: VideoFundsApp/1.0'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    if ($data) $headers[] = 'Content-Type: application/json';
    $options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 30
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ];
    if ($data) $options['http']['content'] = json_encode($data);
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $httpCode = 0;
    if (isset($http_response_header[0])) {
        preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $http_response_header[0], $matches);
        $httpCode = (int)$matches[1];
    }
    return ['response' => $response, 'http_code' => $httpCode];
}

$firestoreUrl = "https://firestore.googleapis.com/v1/projects/" . FIRESTORE_PROJECT_ID . "/databases/(default)/documents/users/$uid";

// Get user document (auto‑create if missing)
$result = firestore_request($firestoreUrl, 'GET', $token);
$userData = json_decode($result['response'], true);

if ($result['http_code'] === 404) {
    $defaultData = [
        'fields' => [
            'fullname'       => ['stringValue' => $_SESSION['user_email'] ?: 'User_' . substr($uid, 0, 6)],
            'email'          => ['stringValue' => $_SESSION['user_email']],
            'balance'        => ['doubleValue' => 5.00],
            'referral_code'  => ['stringValue' => substr($uid, 0, 8)],
            'referred_by'    => ['stringValue' => ''],
            'country_code'   => ['stringValue' => 'TZ'],
            'currency_code'  => ['stringValue' => 'TZS'],
            'exchange_rate'  => ['doubleValue' => 2600.0],
            'created_at'     => ['timestampValue' => date('c')],
            'daily_watch_count' => ['integerValue' => 0],
            'last_watch_date'   => ['stringValue' => date('Y-m-d')],
            'referral_bonus_claimed' => ['booleanValue' => false]
        ]
    ];
    firestore_request($firestoreUrl, 'PUT', $token, $defaultData);
    $result = firestore_request($firestoreUrl, 'GET', $token);
    $userData = json_decode($result['response'], true);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$reward      = floatval($input['reward'] ?? 0);
$videoTitle  = trim($input['video_title'] ?? '');
$videoId     = trim($input['video_youtube_id'] ?? '');

if ($reward <= 0 || empty($videoId)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Check if video already watched
$watchedDocUrl = $firestoreUrl . "/watched_videos/$videoId";
$watchedResult = firestore_request($watchedDocUrl, 'GET', $token);
if ($watchedResult['http_code'] === 200) {
    echo json_encode(['success' => false, 'message' => 'You have already watched this video']);
    exit;
}

// Get current user data
$fields = $userData['fields'];
$currentBalance = floatval($fields['balance']['doubleValue'] ?? 0);
$dailyCount     = intval($fields['daily_watch_count']['integerValue'] ?? 0);
$lastDate       = $fields['last_watch_date']['stringValue'] ?? '';
$today = date('Y-m-d');
if ($lastDate !== $today) {
    $dailyCount = 0;
}
if ($dailyCount >= 50) {
    echo json_encode(['success' => false, 'message' => 'Daily limit reached (50 videos). Come back tomorrow.']);
    exit;
}

// Update balance, daily count, last watch date
$newBalance = $currentBalance + $reward;
$newDailyCount = $dailyCount + 1;
$updateData = [
    'fields' => [
        'balance'            => ['doubleValue' => $newBalance],
        'daily_watch_count'  => ['integerValue' => $newDailyCount],
        'last_watch_date'    => ['stringValue' => $today]
    ]
];
$updateUrl = $firestoreUrl . '?updateMask.fieldPaths=balance&updateMask.fieldPaths=daily_watch_count&updateMask.fieldPaths=last_watch_date';
$updateResult = firestore_request($updateUrl, 'PATCH', $token, $updateData);
if ($updateResult['http_code'] !== 200) {
    echo json_encode(['success' => false, 'message' => 'Failed to update balance']);
    exit;
}

// Record the watched video
$watchedRecord = [
    'fields' => [
        'video_id'    => ['stringValue' => $videoId],
        'title'       => ['stringValue' => $videoTitle],
        'reward'      => ['doubleValue' => $reward],
        'watched_at'  => ['timestampValue' => date('c')]
    ]
];
firestore_request($watchedDocUrl, 'PATCH', $token, $watchedRecord);

// Return success
echo json_encode([
    'success'     => true,
    'new_balance' => $newBalance,
    'message'     => 'Earned $' . number_format($reward, 2)
]);
?>