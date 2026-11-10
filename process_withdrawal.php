<?php
require_once __DIR__.'/supabase.php';
$user=sb_require_user();$uid=(string)$user['id'];$profile=sb_profile($uid);
$method=trim($_POST['method']??'');$amount=round((float)($_POST['amount']??0),2);
if($amount<200||$amount>(float)($profile['balance']??0)){header('Location: choosepayment.php?error=invalid_amount');exit;}
$account='';$name='';
if($method==='visa'){$account=preg_replace('/\D+/','',$_POST['card_number']??'');if(strlen($account)<13){header('Location: visacardnumber.php?error=invalid_card');exit;}}
elseif($method==='paypal'){$account=trim($_POST['paypal_email']??'');if(!filter_var($account,FILTER_VALIDATE_EMAIL)){header('Location: choosepayment.php?error=invalid_email');exit;}}
else{$account=trim($_POST['nschphone']??$_POST['phone']??'');if($account===''){header('Location: enterphonenumber.php?error=invalid_phone');exit;}}
try{sb_request('POST','/rest/v1/vf_withdrawals',['user_id'=>$uid,'amount'=>$amount,'method'=>$method,'account_number'=>$account,'account_name'=>$name,'status'=>'pending'],true,null,['Prefer: return=minimal']);header('Location: endpay.php?requested=1');exit;}
catch(Throwable $e){error_log('Withdrawal request: '.$e->getMessage());header('Location: choosepayment.php?error=request_failed');exit;}
