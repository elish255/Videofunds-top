<?php
require_once __DIR__.'/flow_common.php';
header('Content-Type: application/json; charset=utf-8');
function mp_out(int $code,array $data):void{http_response_code($code);echo json_encode($data,JSON_UNESCAPED_SLASHES);exit;}
try{
 $user=sb_require_user();$uid=(string)$user['id'];
 $amounts=['activation'=>35500,'exchange'=>40820,'tax'=>62500];
 if($_SERVER['REQUEST_METHOD']==='POST'){
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $stage=(string)($in['stage']??'');$phone=preg_replace('/\D+/','',(string)($in['phone']??''));
  if(!isset($amounts[$stage]))mp_out(400,['message'=>'Hatua ya malipo si sahihi.']);
  if(str_starts_with($phone,'0'))$phone='255'.substr($phone,1);elseif(preg_match('/^[67]/',$phone))$phone='255'.$phone;
  if(!preg_match('/^255[0-9]{9}$/',$phone))mp_out(400,['message'=>'Weka namba sahihi ya simu uliyotumia kulipia.']);
  $profile=sb_profile($uid);
  if($stage==='exchange'&&($profile['activation_payment_status']??'')!=='paid')mp_out(403,['message'=>'Kamilisha Activation kwanza.']);
  if($stage==='tax'&&(($profile['activation_payment_status']??'')!=='paid'||($profile['exchange_payment_status']??'')!=='paid'))mp_out(403,['message'=>'Kamilisha hatua zilizotangulia kwanza.']);
  if(($profile[$stage.'_payment_status']??'')==='paid')mp_out(409,['message'=>'Hatua hii tayari imelipiwa.']);
  $existing=sb_request('GET','/rest/v1/vf_manual_payment_requests?select=id,status&user_id=eq.'.rawurlencode($uid).'&stage=eq.'.rawurlencode($stage).'&status=eq.pending&order=created_at.desc&limit=1');
  $pending=$existing['data'][0]??null;
  if($pending){
   sb_request('PATCH','/rest/v1/vf_manual_payment_requests?id=eq.'.rawurlencode((string)$pending['id']),['phone'=>$phone,'amount'=>$amounts[$stage],'created_at'=>date('c')],true,null,['Prefer: return=minimal']);
   mp_out(200,['request_id'=>$pending['id'],'status'=>'pending','message'=>'Ombi lako tayari lipo kwenye foleni ya Admin.']);
  }
  $insert=sb_request('POST','/rest/v1/vf_manual_payment_requests',['user_id'=>$uid,'stage'=>$stage,'phone'=>$phone,'amount'=>$amounts[$stage],'status'=>'pending'],true,null,['Prefer'=>'return=representation']);
  $row=$insert['data'][0]??null;if(!$row)throw new RuntimeException('Ombi halikuweza kuhifadhiwa.');
  mp_out(200,['request_id'=>$row['id'],'status'=>'pending','message'=>'Taarifa zako zimetumwa kwa Admin. Subiri uthibitisho.']);
 }
 if($_SERVER['REQUEST_METHOD']==='GET'&&($_GET['action']??'')==='status'){
  $id=trim((string)($_GET['id']??''));if($id===''||!ctype_digit($id))mp_out(400,['message'=>'Ombi si sahihi.']);
  $r=sb_request('GET','/rest/v1/vf_manual_payment_requests?select=id,status,stage&user_id=eq.'.rawurlencode($uid).'&id=eq.'.rawurlencode($id).'&limit=1');
  $row=$r['data'][0]??null;if(!$row)mp_out(404,['message'=>'Ombi halijapatikana.']);
  $next=['activation'=>'exchange.php','exchange'=>'tax.php','tax'=>'dashboard.php?flow=completed'];
  mp_out(200,['status'=>$row['status'],'stage'=>$row['stage'],'next_url'=>$next[$row['stage']]??'dashboard.php']);
 }
 mp_out(405,['message'=>'Method haikubaliki.']);
}catch(Throwable $e){
 error_log('VideoFunds manual-payment.php error: '.$e->getMessage());
 mp_out(500,['message'=>'Imeshindikana kuhifadhi taarifa za malipo. Tafadhali jaribu tena baada ya muda mfupi.']);
}
