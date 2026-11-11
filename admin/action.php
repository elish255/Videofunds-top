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
 if(in_array($action,['remove_activation','remove_exchange','remove_tax'],true)){
  if($uid==='')throw new RuntimeException('UID inahitajika');
  $profile=get_user($uid)['profile'];
  $updates=['updated_at'=>date('c')];
  if($action==='remove_activation'){
   $updates=array_merge($updates,['activated'=>false,'activation_payment_status'=>'unpaid','activation_paid_at'=>null,'activation_payment_reference'=>null,'exchange_payment_status'=>'unpaid','exchange_paid_at'=>null,'exchange_payment_reference'=>null,'tax_payment_status'=>'unpaid','tax_paid_at'=>null,'tax_payment_reference'=>null,'membership'=>'ACTIVATION','withdrawal_flow_stage'=>'activation']);
  }elseif($action==='remove_exchange'){
   $updates=array_merge($updates,['exchange_payment_status'=>'unpaid','exchange_paid_at'=>null,'exchange_payment_reference'=>null,'tax_payment_status'=>'unpaid','tax_paid_at'=>null,'tax_payment_reference'=>null,'membership'=>'EXCHANGE','withdrawal_flow_stage'=>'exchange']);
  }else{
   $updates=array_merge($updates,['tax_payment_status'=>'unpaid','tax_paid_at'=>null,'tax_payment_reference'=>null,'membership'=>'TAX','withdrawal_flow_stage'=>'tax']);
  }
  sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),$updates,true,null,['Prefer: return=minimal']);
  audit($action,$uid);header('Location: /admin/?updated=1#users');exit;
 }
 if($action==='toggle_activation'){if($uid==='')throw new RuntimeException('UID inahitajika');$value=($_POST['value']??'0')==='1';sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['activated'=>$value,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);audit($value?'activate':'deactivate',$uid);header('Location: /admin/?updated=1');exit;}
 if($action==='set_membership'){if($uid==='')throw new RuntimeException('UID inahitajika');$membership=strtoupper(trim($_POST['membership']??'ACTIVATION'));if(!in_array($membership,['ACTIVATION','EXCHANGE','TAX'],true))throw new RuntimeException('Membership si sahihi');sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['membership'=>$membership,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);audit('membership',$uid,['membership'=>$membership]);header('Location: /admin/?updated=1');exit;}
 if($action==='balance'){if($uid==='')throw new RuntimeException('UID inahitajika');$kind=$_POST['kind']??'';$amount=round((float)($_POST['amount']??0),2);if(!in_array($kind,['credit','debit'],true)||$amount<=0||$amount>1000000)throw new RuntimeException('Kiasi si sahihi');$p=get_user($uid)['profile'];$old=(float)($p['balance']??0);if($kind==='debit'&&$amount>$old)throw new RuntimeException('Balance haitoshi kwa punguzo hilo.');$new=$kind==='credit'?$old+$amount:$old-$amount;sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),['balance'=>$new,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);sb_request('POST','/rest/v1/vf_transactions',[['user_id'=>$uid,'type'=>$kind==='credit'?'admin_credit':'admin_debit','amount'=>$kind==='credit'?$amount:-$amount,'description'=>'Admin '.($kind==='credit'?'added':'reduced').' balance','reference'=>'ADM-'.bin2hex(random_bytes(10))]],true,null,['Prefer: return=minimal']);header('Location: /admin/?updated=1');exit;}
 if($action==='delete_many'){$uids=$_POST['uids']??[];if(!is_array($uids)||!$uids)throw new RuntimeException('Hakuna account iliyochaguliwa');$uids=array_values(array_filter(array_map('trim',$uids),fn($v)=>$v!==''&&preg_match('/^[0-9a-fA-F-]{20,}$/',$v)));if(!$uids)throw new RuntimeException('UID haziko sahihi');foreach($uids as $id)sb_request('DELETE','/auth/v1/admin/users/'.rawurlencode($id),null,true,null);header('Location: /admin/?deleted=1');exit;}
 if($action==='notify_all'||$action==='notify_one'){$title=trim($_POST['title']??'');$body=trim($_POST['body']??'');if($title===''||$body==='')throw new RuntimeException('Kichwa na ujumbe vinahitajika');$rows=[];if($action==='notify_one'){if($uid==='')throw new RuntimeException('UID inahitajika');$rows[]=['user_id'=>$uid,'title'=>$title,'body'=>$body];}else{$users=sb_request('GET','/rest/v1/vf_profiles?select=id&limit=1000')['data']??[];foreach($users as $u)$rows[]=['user_id'=>$u['id'],'title'=>$title,'body'=>$body];}if($rows)sb_request('POST','/rest/v1/vf_notifications',$rows,true,null,['Prefer: return=minimal']);header('Location: /admin/?sent=1');exit;}
 if(in_array($action,['manual_payment_approve','manual_payment_reject'],true)){
  $rid=trim((string)($_POST['request_id']??''));if($rid===''||!ctype_digit($rid))throw new RuntimeException('Ombi la malipo si sahihi.');
  $r=sb_request('GET','/rest/v1/vf_manual_payment_requests?select=*&id=eq.'.rawurlencode($rid).'&limit=1');$request=$r['data'][0]??null;
  if(!$request)throw new RuntimeException('Ombi halijapatikana.');
  if(($request['status']??'')!=='pending')throw new RuntimeException('Ombi hili tayari limeshughulikiwa.');
  $stage=(string)$request['stage'];$uid=(string)$request['user_id'];$profile=get_user($uid)['profile'];
  if(!in_array($stage,['activation','exchange','tax'],true))throw new RuntimeException('Hatua ya malipo si sahihi.');
  if($action==='manual_payment_approve'){
   if($stage==='exchange'&&($profile['activation_payment_status']??'')!=='paid')throw new RuntimeException('Activation haijathibitishwa.');
   if($stage==='tax'&&(($profile['activation_payment_status']??'')!=='paid'||($profile['exchange_payment_status']??'')!=='paid'))throw new RuntimeException('Hatua zilizotangulia hazijathibitishwa.');
   $next=['activation'=>'exchange','exchange'=>'tax','tax'=>'completed'][$stage];$membership=['activation'=>'EXCHANGE','exchange'=>'TAX','tax'=>'TAX'][$stage];
   $updates=[$stage.'_payment_status'=>'paid',$stage.'_payment_reference'=>'MANUAL-'.$rid,$stage.'_paid_at'=>date('c'),'withdrawal_flow_stage'=>$next,'membership'=>$membership,'updated_at'=>date('c')];if($stage==='activation')$updates['activated']=true;
   sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),$updates,true,null,['Prefer: return=minimal']);
   sb_request('PATCH','/rest/v1/vf_manual_payment_requests?id=eq.'.rawurlencode($rid),['status'=>'approved','reviewed_at'=>date('c')],true,null,['Prefer: return=minimal']);
   sb_request('POST','/rest/v1/vf_notifications',[['user_id'=>$uid,'title'=>'Malipo yamethibitishwa','body'=>'Admin amethibitisha malipo ya '.$stage.'. Unaweza kuendelea na hatua inayofuata.']],true,null,['Prefer: return=minimal']);
   audit('manual_payment_approved',$uid,['request_id'=>$rid,'stage'=>$stage]);
  }else{
   $updates=[$stage.'_payment_status'=>'unpaid',$stage.'_payment_reference'=>null,$stage.'_paid_at'=>null,'withdrawal_flow_stage'=>$stage,'membership'=>strtoupper($stage),'updated_at'=>date('c')];if($stage==='activation')$updates['activated']=false;
   sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),$updates,true,null,['Prefer: return=minimal']);
   sb_request('PATCH','/rest/v1/vf_manual_payment_requests?id=eq.'.rawurlencode($rid),['status'=>'rejected','reviewed_at'=>date('c')],true,null,['Prefer: return=minimal']);
   sb_request('POST','/rest/v1/vf_notifications',[['user_id'=>$uid,'title'=>'Malipo hayajathibitishwa','body'=>'Malipo ya '.$stage.' hayajathibitishwa. Tafadhali lipia tena na utume taarifa upya.']],true,null,['Prefer: return=minimal']);
   audit('manual_payment_rejected',$uid,['request_id'=>$rid,'stage'=>$stage]);
  }
  header('Location: /admin/?updated=1#manual-payments');exit;
 }
 if($action==='withdrawal_set'){
  $wid=trim((string)($_POST['withdrawal_id']??''));$status=strtolower(trim((string)($_POST['status']??'')));
  if($wid===''||!ctype_digit($wid))throw new RuntimeException('Withdrawal si sahihi.');
  if(!in_array($status,['approved','paid','rejected'],true))throw new RuntimeException('Status si sahihi.');
  $res=sb_rpc('vf_admin_set_withdrawal',['p_id'=>(int)$wid,'p_status'=>$status,'p_note'=>trim((string)($_POST['note']??''))]);
  $wuid=(string)($res['user_id']??'');$wamt=number_format((float)($res['amount']??0),2);
  $texts=['approved'=>['Withdrawal imekubaliwa','Ombi lako la kutoa $'.$wamt.' limekubaliwa (Approved). Malipo yatatumwa hivi karibuni.'],'paid'=>['Withdrawal imelipwa','Ombi lako la kutoa $'.$wamt.' limelipwa (Paid). Tafadhali angalia akaunti yako.'],'rejected'=>['Withdrawal imekataliwa','Ombi lako la kutoa $'.$wamt.' limekataliwa. Kiasi kimerudishwa kwenye balance yako.']][$status];
  if($wuid!=='')sb_request('POST','/rest/v1/vf_notifications',[['user_id'=>$wuid,'title'=>$texts[0],'body'=>$texts[1]]],true,null,['Prefer: return=minimal']);
  audit('withdrawal_'.$status,$wuid,['withdrawal_id'=>$wid]);
  header('Location: /admin/?updated=1#withdrawals');exit;
 }
 throw new RuntimeException('Action haijulikani');
}catch(Throwable $e){http_response_code(400);echo '<!doctype html><meta charset="utf-8"><body style="font-family:Arial;background:#06101d;color:white;padding:25px"><h2>Admin action failed</h2><p>'.h($e->getMessage()).'</p><a style="color:#55e0c2" href="/admin/">Rudi Admin</a></body>';}
?>