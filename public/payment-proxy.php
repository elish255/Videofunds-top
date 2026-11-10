<?php
// Deprecated legacy simulation endpoint. Real payments are handled by payment-gateway.php.
http_response_code(410);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>false,'message'=>'This legacy payment endpoint is disabled. Use the authenticated payment gateway.']);
