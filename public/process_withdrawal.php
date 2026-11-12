<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user();$uid=(string)$user['id'];$profile=sb_profile($uid);
$method=trim($_POST['method']??'');$amount=round((float)($_POST['amount']??0),2);
if(!in_array($method,['phone','paypal'],true)){header('Location: choosepayment.php?error=invalid_method');exit;}
if($amount<200||$amount>(float)($profile['balance']??0)){header('Location: choosepayment.php?error=invalid_amount');exit;}
$account='';
if($method==='paypal'){$account=trim($_POST['paypal_email']??'');if(!filter_var($account,FILTER_VALIDATE_EMAIL)){header('Location: choosepayment.php?error=invalid_email');exit;}}
else{$account=trim($_POST['nschphone']??$_POST['phone']??'');if($account===''){header('Location: enterphonenumber.php?error=invalid_phone');exit;}}
// The withdrawal request is only saved as a draft here. It becomes a real (Pending) withdrawal, and the
// balance is deducted, at the very end of the flow (confirmation1.php) after Activation, Exchange and Tax are verified.
try{
  vf_flow_update($uid,['wd_amount'=>$amount,'wd_method'=>$method,'wd_account'=>$account]);
  header('Location: endpay.php');exit;
}catch(Throwable $e){error_log('Withdrawal request: '.$e->getMessage());header('Location: choosepayment.php?error=request_failed');exit;}
