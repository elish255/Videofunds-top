<?php
// Include after session_start() on authenticated user pages.
if (!empty($_SESSION['user_id']) && !empty($_SESSION['id_token'])) {
    $uid = rawurlencode((string)$_SESSION['user_id']);
    $url = 'https://firestore.googleapis.com/v1/projects/'.rawurlencode(FIRESTORE_PROJECT_ID).'/databases/(default)/documents/users/'.$uid;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$_SESSION['id_token']], CURLOPT_TIMEOUT=>12]);
    $raw = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $doc = json_decode((string)$raw, true);
    $banned = $doc['fields']['banned']['booleanValue'] ?? false;
    if ($banned === true || $code === 401 || $code === 403) {
        $_SESSION = [];
        session_destroy();
        header('Location: /index.php?error=account_unavailable');
        exit;
    }
}
