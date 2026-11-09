<?php
session_start();
if (empty($_SESSION['user_id'])) { http_response_code(401); header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
// Payment endpoint deliberately fails closed until the real provider endpoint is configured.
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
$access=getenv('PAYMENT_ACCESS_KEY') ?: ''; $secret=getenv('PAYMENT_SECRET_KEY') ?: ''; $endpoint=getenv('PAYMENT_API_URL') ?: '';
if (!$access || !$secret || !$endpoint) { http_response_code(503); echo json_encode(['success'=>false,'message'=>'Payment provider is not configured yet. Please contact support.']); exit; }
$input=json_decode(file_get_contents('php://input'),true) ?: [];
if (($input['action']??'')==='initiate_payment') {
  $phone=preg_replace('/\D+/', '', (string)($input['phone_number']??'')); $amount=(float)($input['amount']??0);
  if (!preg_match('/^(255)?(6|7)[0-9]{8}$/',$phone) || $amount<=0) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid phone number or amount']); exit; }
  $payload=['access_key'=>$access,'secret_key'=>$secret,'phone_number'=>$phone,'amount'=>$amount,'currency'=>'TZS','transaction_ref'=>'VF_'.bin2hex(random_bytes(8)),'merchant_id'=>getenv('PAYMENT_MERCHANT_ID') ?: ''];
  $ch=curl_init($endpoint); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$access],CURLOPT_TIMEOUT=>30]); $raw=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $r=json_decode((string)$raw,true) ?: [];
  if ($code>=200 && $code<300) { echo json_encode(['success'=>true,'message'=>'Payment request sent.','transaction_id'=>$r['transaction_id']??$payload['transaction_ref'],'status'=>'pending']); } else { http_response_code(502); echo json_encode(['success'=>false,'message'=>'Payment provider returned an error.']); } exit;
}
if (($input['action']??'')==='check_status') { $statusUrl=getenv('PAYMENT_STATUS_URL') ?: ''; if(!$statusUrl){http_response_code(503);echo json_encode(['success'=>false,'message'=>'Payment status endpoint is not configured']);exit;} $ch=curl_init($statusUrl);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode(['access_key'=>$access,'secret_key'=>$secret,'transaction_id'=>$input['transaction_id']??'']),CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_TIMEOUT=>20]);$raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$r=json_decode((string)$raw,true)?:[];if($code>=400){http_response_code(502);echo json_encode(['success'=>false,'status'=>'unknown']);}else echo json_encode(['success'=>true,'status'=>strtolower($r['status']??'pending')]);exit;}
http_response_code(400);echo json_encode(['success'=>false,'message'=>'Unknown action']);
?>