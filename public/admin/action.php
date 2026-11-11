<?php
require_once __DIR__.'/../supabase.php';require_once __DIR__.'/_lib.php';require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
try{
 check_csrf(); $action=$_POST['action']??''; $uid=trim($_POST['uid']??'');
 if(in_array($action,['ban','unban'],true)){if($uid==='')throw new RuntimeException('UID inahitajika');sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['banned'=>$action==='ban','updated_at'=>date('c')],true,null,['Prefer: return=minimal']);audit($action,$uid);header('Location: /admin/?updated=1');exit;}
 if($action==='make_active'){
  if($uid==='')throw new RuntimeException('UID inahitajika');
  sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['activated'=>true,'activation_payment_status'=>'paid','activation_paid_at'=>date('c'),'withdrawal_flow_stage'=>'exchange','membership'=>'EXCHANGE','updated_at'=>date('c')],true,null,['Prefer: return=minimal']);
  audit('activation_admin_verified',$uid);header('Location: /admin/?updated=1#users');exit;
 }
 if($action==='exchange_paid'){
  if($uid==='')throw new RuntimeException('UID inahitajika');
  $profile=get_user($uid)['profile'];
  if((string)($profile['activation_payment_status']??'')!=='paid' && empty($profile['activated']))throw new RuntimeException('Thibitisha Activation kwanza.');
  sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['exchange_payment_status'=>'paid','exchange_paid_at'=>date('c'),'withdrawal_flow_stage'=>'tax','membership'=>'TAX','updated_at'=>date('c')],true,null,['Prefer: return=minimal']);
  audit('exchange_admin_verified',$uid);header('Location: /admin/?updated=1#users');exit;
 }
 if($action==='tax_paid'){
  if($uid==='')throw new RuntimeException('UID inahitajika');
  $profile=get_user($uid)['profile'];
  if((string)($profile['activation_payment_status']??'')!=='paid' && empty($profile['activated']))throw new RuntimeException('Thibitisha Activation kwanza.');
  if((string)($profile['exchange_payment_status']??'')!=='paid')throw new RuntimeException('Thibitisha Exchange kwanza.');
  sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['tax_payment_status'=>'paid','tax_paid_at'=>date('c'),'withdrawal_flow_stage'=>'withdrawal_processing','membership'=>'TAX','updated_at'=>date('c')],true,null,['Prefer: return=minimal']);
  audit('tax_admin_verified',$uid);header('Location: /admin/?updated=1#users');exit;
 }
 if($action==='toggle_activation'){if($uid==='')throw new RuntimeException('UID inahitajika');$value=($_POST['value']??'0')==='1';sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['activated'=>$value,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);audit($value?'activate':'deactivate',$uid);header('Location: /admin/?updated=1');exit;}
 if($action==='set_membership'){if($uid==='')throw new RuntimeException('UID inahitajika');$membership=strtoupper(trim($_POST['membership']??'ACTIVATION'));if(!in_array($membership,['ACTIVATION','EXCHANGE','TAX'],true))throw new RuntimeException('Membership si sahihi');sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['membership'=>$membership,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);audit('membership',$uid,['membership'=>$membership]);header('Location: /admin/?updated=1');exit;}
 if($action==='balance'){if($uid==='')throw new RuntimeException('UID inahitajika');$kind=$_POST['kind']??'';$amount=round((float)($_POST['amount']??0),2);if(!in_array($kind,['credit','debit'],true)||$amount<=0||$amount>1000000)throw new RuntimeException('Kiasi si sahihi');$p=get_user($uid)['profile'];$old=(float)($p['balance']??0);if($kind==='debit'&&$amount>$old)throw new RuntimeException('Balance haitoshi kwa punguzo hilo.');$new=$kind==='credit'?$old+$amount:$old-$amount;sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['balance'=>$new,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);sb_request('POST','/rest/v1/vf_transactions',[['user_id'=>$uid,'type'=>$kind==='credit'?'admin_credit':'admin_debit','amount'=>$kind==='credit'?$amount:-$amount,'description'=>'Admin '.($kind==='credit'?'added':'reduced').' balance','reference'=>'ADM-'.bin2hex(random_bytes(10))]],true,null,['Prefer: return=minimal']);header('Location: /admin/?updated=1');exit;}
 if($action==='delete_many'){$uids=$_POST['uids']??[];if(!is_array($uids)||!$uids)throw new RuntimeException('Hakuna account iliyochaguliwa');$uids=array_values(array_filter(array_map('trim',$uids),fn($v)=>$v!==''&&preg_match('/^[0-9a-fA-F-]{20,}$/',$v)));if(!$uids)throw new RuntimeException('UID haziko sahihi');foreach($uids as $id)sb_request('DELETE','/auth/v1/admin/users/'.rawurlencode($id),null,true,null);header('Location: /admin/?deleted=1');exit;}
 if($action==='notify_all'||$action==='notify_one'){$title=trim($_POST['title']??'');$body=trim($_POST['body']??'');if($title===''||$body==='')throw new RuntimeException('Kichwa na ujumbe vinahitajika');$rows=[];if($action==='notify_one'){if($uid==='')throw new RuntimeException('UID inahitajika');$rows[]=['user_id'=>$uid,'title'=>$title,'body'=>$body];}else{$users=sb_request('GET','/rest/v1/vf_profiles?select=id&limit=1000')['data']??[];foreach($users as $u)$rows[]=['user_id'=>$u['id'],'title'=>$title,'body'=>$body];}if($rows)sb_request('POST','/rest/v1/vf_notifications',$rows,true,null,['Prefer: return=minimal']);header('Location: /admin/?sent=1');exit;}
 throw new RuntimeException('Action haijulikani');
}catch(Throwable $e){http_response_code(400);echo '<!doctype html><meta charset="utf-8"><body style="font-family:Arial;background:#06101d;color:white;padding:25px"><h2>Admin action failed</h2><p>'.h($e->getMessage()).'</p><a style="color:#55e0c2" href="/admin/">Rudi Admin</a></body>';}
?>