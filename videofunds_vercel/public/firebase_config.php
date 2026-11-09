<?php
// Public Firebase web API key is configured through Vercel Environment Variables.
$vfFirebaseKey = getenv('FIREBASE_API_KEY');
$vfProjectId = getenv('FIREBASE_PROJECT_ID') ?: 'videofunds-project';
if (!$vfFirebaseKey) {
    http_response_code(503);
    exit('Firebase configuration is incomplete. Set FIREBASE_API_KEY in Vercel.');
}
define('FIREBASE_API_KEY', $vfFirebaseKey);
define('FIRESTORE_PROJECT_ID', $vfProjectId);
function firestore_patch($url, $data, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http >= 200 && $http < 300;
}
?>
