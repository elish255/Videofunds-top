<?php
/** Real SplashPay mobile-money integration. All credentials stay server-side. */
require_once __DIR__.'/flow_common.php';
header('Content-Type: application/json; charset=utf-8');
function sp_out(int $code,array $data): void { http_response_code($code); echo json_encode($data,JSON_UNESCAPED_SLASHES); exit; }
function sp_env(string $key): string { return trim((string)getenv($key)); }
function sp_provider(string $path,array $payload,string $reference=''): array {
  $key=sp_env('SPLASHPAY_API_KEY'); $secret=sp_env('SPLASHPAY_API_SECRET');
  if($key===''||$secret==='') throw new RuntimeException('SplashPay API credentials are not configured on the server.');
  $headers=['Content-Type: application/json','Accept: application/json','X-API-KEY: '.$key,'X-API-SECRET: '.$secret];
  if($reference!=='') $headers[]='Idempotency-Key: '.$reference;
  $ch=curl_init('https://api.splashpay.co.tz/api/v1'.$path);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>35,CURLOPT_CONNECTTIMEOUT=>10]);
  $raw=curl_exec($ch);$http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
  if($raw===false) throw new RuntimeException('SplashPay connection failed: '.$err);
  $data=json_decode($raw,true); if(!is_array($data)) throw new RuntimeException('SplashPay returned an invalid response.');
  return [$http,$data];
}
function sp_profile_update(string $uid,array $data): void { vf_flow_update($uid,$data); }
function sp_complete(array $payment,?string $providerRef): void {
  $stage=(string)$payment['stage'];$uid=(string)$payment['user_id'];
  $profile=sb_profile($uid);$field=$stage.'_payment_status';$refField=$stage.'_payment_reference';
  if(($profile[$field]??'')==='paid') return;
  $next=['activation'=>'exchange','exchange'=>'tax','tax'=>'withdrawal_processing'][$stage];
  $membershipAfter=['activation'=>'EXCHANGE','exchange'=>'TAX','tax'=>'TAX'][$stage];
  $updates=[$field=>'paid',$refField=>$payment['order_id'],$stage.'_paid_at'=>date('c'),'withdrawal_flow_stage'=>$next,'membership'=>$membershipAfter];
  if($stage==='activation') $updates['activated']=true;
  sp_profile_update($uid,$updates);
  sb_request('PATCH','/rest/v1/vf_payments?id=eq.'.rawurlencode((string)$payment['id']),['status'=>'success','provider_reference'=>$providerRef,'completed_at'=>date('c')],true,null,['Prefer: return=minimal']);
}
try {
  if($_SERVER['REQUEST_METHOD']!=='POST') sp_out(405,['message'=>'Method not allowed']);
  $user=sb_require_user();$uid=(string)$user['id'];$input=json_decode(file_get_contents('php://input'),true)?:[];$action=(string)($input['action']??'');
  $amounts=['activation'=>35500,'exchange'=>40820,'tax'=>62500];
  if($action==='create') {
    $stage=(string)($input['stage']??'');$phone=preg_replace('/\D+/','',(string)($input['phone']??''));
    if(!isset($amounts[$stage])) sp_out(400,['message'=>'Aina ya malipo si sahihi.']);
    if(!preg_match('/^255[0-9]{9}$/',$phone)) sp_out(400,['message'=>'Weka namba sahihi ya Tanzania, mfano 255712345678.']);
    $profile=sb_profile($uid);vf_demo_require_balance($profile);
    if($stage==='exchange' && ($profile['activation_payment_status']??'')!=='paid') sp_out(403,['message'=>'Kamilisha activation kwanza.']);
    if($stage==='tax' && (($profile['activation_payment_status']??'')!=='paid'||($profile['exchange_payment_status']??'')!=='paid')) sp_out(403,['message'=>'Kamilisha hatua zilizotangulia kwanza.']);
    if(($profile[$stage.'_payment_status']??'')==='paid') sp_out(409,['message'=>'Hatua hii tayari imelipiwa.']);
    $amount=$amounts[$stage];$reference='VF-'.strtoupper(bin2hex(random_bytes(12)));
    $ins=sb_request('POST','/rest/v1/vf_payments',['user_id'=>$uid,'stage'=>$stage,'order_id'=>$reference,'amount'=>$amount,'currency'=>'TZS','phone'=>$phone,'status'=>'pending'],true,null,['Prefer: return=representation']);
    $payment=$ins['data'][0]??null;if(!$payment) throw new RuntimeException('Could not save payment request.');
    [$http,$provider]=sp_provider('/payments/mobile-money',['amount'=>$amount,'currency'=>'TZS','reference'=>$reference,'phone'=>$phone,'customer_name'=>$profile['full_name']??'VideoFunds User','customer_email'=>$user['email']??'','metadata'=>['user_id'=>$uid,'stage'=>$stage]],$reference);
    $result=$provider['data']??$provider;
    if($http<200||$http>=300||strtolower((string)($provider['status']??''))!=='success') {
      sb_request('PATCH','/rest/v1/vf_payments?id=eq.'.rawurlencode((string)$payment['id']),['status'=>'failed'],true,null,['Prefer: return=minimal']);
      sp_out(502,['message'=>'Imeshindikana kuanzisha malipo. Tafadhali jaribu tena.']);
    }
    $providerRef=$result['provider_reference']??$result['transaction_reference']??$result['transaction_id']??$result['id']??null;
    $initialStatus=strtolower((string)($result['status']??'pending'));if(!in_array($initialStatus,['pending','processing','success','failed','cancelled','expired'],true))$initialStatus='pending';
    sb_request('PATCH','/rest/v1/vf_payments?id=eq.'.rawurlencode((string)$payment['id']),['provider_reference'=>$providerRef,'status'=>$initialStatus],true,null,['Prefer: return=minimal']);
    sp_profile_update($uid,[$stage.'_payment_status'=>'pending',$stage.'_payment_reference'=>$reference,'withdrawal_flow_stage'=>$stage,'membership'=>strtoupper($stage)]);
    sp_out(200,['reference'=>$reference,'payment_status'=>$initialStatus,'message'=>'Ombi limetumwa. Thibitisha muamala kwenye simu yako.']);
  }
  if($action==='status') {
    $reference=trim((string)($input['reference']??''));if($reference==='') sp_out(400,['message'=>'Reference haipo.']);
    $r=sb_request('GET','/rest/v1/vf_payments?select=id,user_id,stage,order_id,amount,currency,status,provider_reference&id=not.is.null&order_id=eq.'.rawurlencode($reference).'&user_id=eq.'.rawurlencode($uid).'&limit=1');
    $payment=$r['data'][0]??null;if(!$payment) sp_out(404,['message'=>'Muamala haujapatikana.']);
    if($payment['status']==='success') sp_out(200,['payment_status'=>'success','reference'=>$reference]);
    [$http,$provider]=sp_provider('/payments/check-status',['reference'=>$reference]);
    if($http<200||$http>=300||strtolower((string)($provider['status']??''))!=='success') sp_out(502,['message'=>'Imeshindikana kuthibitisha malipo kwa sasa.']);
    $result=$provider['data']??[];$returned=(string)($result['reference']??$result['merchant_reference']??'');
    if(abs((float)($result['amount']??0)-(float)$payment['amount'])>0.009||strtoupper((string)($result['currency']??''))!=='TZS'||($returned!==''&&$returned!==$reference)) sp_out(409,['message'=>'Taarifa za malipo hazilingani. Wasiliana na huduma kwa wateja.']);
    $status=strtolower((string)($result['status']??'pending'));if(!in_array($status,['pending','processing','success','failed','cancelled','expired'],true))$status='pending';$dbStatus=$status==='processing'?'pending':$status;$providerRef=$result['provider_reference']??$result['transaction_reference']??$result['transaction_id']??$result['id']??null;
    if($status==='success') sp_complete($payment,$providerRef);
    else sb_request('PATCH','/rest/v1/vf_payments?id=eq.'.rawurlencode((string)$payment['id']),['status'=>$dbStatus,'provider_reference'=>$providerRef],true,null,['Prefer: return=minimal']);
    sp_out(200,['payment_status'=>$status,'reference'=>$reference]);
  }
  sp_out(400,['message'=>'Ombi halitambuliki.']);
} catch(Throwable $e) { error_log('SplashPay integration: '.$e->getMessage()); sp_out(500,['message'=>'Kuna hitilafu kwenye huduma ya malipo. Tafadhali jaribu tena baadaye.']); }
