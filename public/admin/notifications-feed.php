<?php
require_once __DIR__.'/../supabase.php';
require_once __DIR__.'/_lib.php';
require_admin();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
try {
 $after=trim((string)($_GET['after']??''));
 if($after==='' || strtotime($after)===false){http_response_code(400);echo json_encode(['error'=>'after timestamp required']);exit;}
 $url='/rest/v1/vf_manual_payment_requests?select=id,user_id,stage,phone,amount,created_at&status=eq.pending&created_at=gt.'.rawurlencode($after).'&order=created_at.asc&limit=50';
 $r=sb_request('GET',$url);
 echo json_encode(['requests'=>$r['data']??[]],JSON_UNESCAPED_SLASHES);
} catch(Throwable $e){error_log('Admin notification feed: '.$e->getMessage());http_response_code(500);echo json_encode(['error'=>'Could not load notifications']);}
