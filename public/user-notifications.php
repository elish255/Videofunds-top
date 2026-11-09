<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
try {
    require_once __DIR__.'/admin/_lib.php';
    $docs = fs('GET','notifications?pageSize=500')['documents'] ?? [];
    $items=[]; $uid=(string)$_SESSION['user_id'];
    foreach($docs as $d){$f=decode_fields($d['fields']??[]);if(($f['target_uid']??'')==='ALL'||($f['target_uid']??'')===$uid){$items[]=['id'=>basename($d['name']),'title'=>$f['title']??'Notification','body'=>$f['body']??'','created_at'=>$f['created_at']??''];}}
    usort($items,fn($a,$b)=>strcmp($b['created_at'],$a['created_at']));
    echo json_encode(['ok'=>true,'items'=>array_slice($items,0,30)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) { http_response_code(503); echo json_encode(['ok'=>false,'error'=>'Notifications are temporarily unavailable']); }
