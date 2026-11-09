<?php
ini_set('display_errors', 0); // Hide errors from output
session_start();
require_once 'firebase_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    // Empty fields – show alert and redirect
    echo "<script>alert('Please enter both email and password.'); window.location.href='index.php';</script>";
    exit;
}

// Firebase sign-in endpoint
$url = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . FIREBASE_API_KEY;
$data = [
    'email'             => $email,
    'password'          => $password,
    'returnSecureToken' => true
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data),
        'ignore_errors' => true,
        'timeout' => 30
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

// Extract HTTP status code
$httpCode = 0;
if (isset($http_response_header[0])) {
    preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $http_response_header[0], $matches);
    $httpCode = (int)$matches[1];
}

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['idToken'])) {
    // Login successful – set session and redirect to dashboard
    $_SESSION['user_id']    = $result['localId'];
    $_SESSION['user_email'] = $result['email'];
    $_SESSION['id_token']   = $result['idToken'];
    require_once __DIR__ . '/account_guard.php';
    header('Location: dashboard.php');
    exit;
} else {
    // Login failed – show pop‑up alert and redirect to login page
    echo "<script>alert('Invalid email or password. Please try again.'); window.location.href='index.php';</script>";
    exit;
}
?>