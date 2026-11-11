<?php
require_once __DIR__.'/supabase.php';

header('Content-Type: application/json; charset=utf-8');

function notification_json(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    notification_json(405, ['success' => false, 'message' => 'Method not allowed']);
}

try {
    $user = sb_require_user();
    $uid = (string)($user['id'] ?? '');
    $token = (string)($_COOKIE['vf_access_token'] ?? '');
    if ($uid === '' || $token === '') {
        notification_json(401, ['success' => false, 'message' => 'Tafadhali ingia tena.']);
    }

    $action = (string)($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        notification_json(400, ['success' => false, 'message' => 'Notification ID si sahihi.']);
    }

    $filter = 'id=eq.'.rawurlencode((string)$id).'&user_id=eq.'.rawurlencode($uid);
    if ($action === 'mark_read') {
        $result = sb_request(
            'PATCH',
            '/rest/v1/vf_notifications?'.$filter,
            ['is_read' => true],
            false,
            $token,
            ['Prefer: return=representation']
        );
        if (empty($result['data'])) {
            notification_json(404, ['success' => false, 'message' => 'Notification haijapatikana.']);
        }
        notification_json(200, ['success' => true]);
    }

    if ($action === 'dismiss') {
        $result = sb_request(
            'DELETE',
            '/rest/v1/vf_notifications?'.$filter,
            null,
            false,
            $token,
            ['Prefer: return=representation']
        );
        if (empty($result['data'])) {
            notification_json(404, ['success' => false, 'message' => 'Notification haijapatikana.']);
        }
        notification_json(200, ['success' => true]);
    }

    notification_json(400, ['success' => false, 'message' => 'Action haijulikani.']);
} catch (Throwable $e) {
    error_log('Notification action: '.$e->getMessage());
    notification_json(500, ['success' => false, 'message' => 'Imeshindikana kutekeleza ombi. Jaribu tena.']);
}
