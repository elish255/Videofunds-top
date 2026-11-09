<?php
session_start();
require_once __DIR__.'/firebase_config.php';
require_once __DIR__.'/admin/_lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$fullname=trim($_POST['fullname']??'');$email=strtolower(trim($_POST['email']??''));$password=$_POST['password']??'';$confirm=$_POST['confirm_password']??'';$submittedRef=strtoupper(trim($_POST['referral_code']??''));$country=trim($_POST['country_code']??'US');$currency=trim($_POST['currency_code']??'USD');$rate=(float)($_POST['exchange_rate']??1.0);
if($fullname===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<6||$password!==$confirm){header('Location: /index.php?error=invalid_registration');exit;}
try {
    // Resolve a valid referral code before creating the account. Never credit the same account.
    $referrerId='';$referrerName='';$referrerEmail='';
    if($submittedRef!==''){
        $docs=fs('GET','users?pageSize=1000')['documents']??[];
        foreach($docs as $doc){$f=decode_fields($doc['fields']??[]);if(strtoupper((string)($f['referral_code']??''))===$submittedRef){$referrerId=basename($doc['name']);$referrerName=(string)($f['fullname']??'User');$referrerEmail=strtolower((string)($f['email']??''));break;}}
        if($referrerId!=='' && $referrerEmail===$email){$referrerId='';}
    }
    $url='https://identitytoolkit.googleapis.com/v1/accounts:signUp?key='.rawurlencode(FIREBASE_API_KEY);$ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode(['email'=>$email,'password'=>$password,'returnSecureToken'=>true]),CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_TIMEOUT=>25]);$raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$auth=json_decode((string)$raw,true)?:[];
    if($code!==200||empty($auth['localId']))throw new RuntimeException($auth['error']['message']??'registration_failed');
    $uid=$auth['localId'];$token=$auth['idToken'];$refCode=strtoupper(substr(bin2hex(random_bytes(5)),0,8));
    $profile=['fullname'=>$fullname,'email'=>$email,'balance'=>5.00,'referral_code'=>$refCode,'referred_by'=>$referrerId!==''?$submittedRef:'','country_code'=>$country,'currency_code'=>$currency,'exchange_rate'=>$rate,'created_at'=>gmdate('c'),'daily_watch_count'=>0,'last_watch_date'=>date('Y-m-d'),'referral_bonus_claimed'=>false,'banned'=>false];
    fs('PATCH','users/'.rawurlencode($uid),typed_fields($profile));
    // Use the new user's unique UID as the bonus document ID to make duplicate credits idempotent.
    if($referrerId!==''){
        fs('PATCH','users/'.rawurlencode($referrerId).'/pending_bonuses/'.rawurlencode($uid),typed_fields(['amount'=>2.00,'from_user_id'=>$uid,'from_user_name'=>$fullname,'created_at'=>gmdate('c'),'claimed'=>false]));
    }
    $_SESSION['user_id']=$uid;$_SESSION['user_email']=$email;$_SESSION['id_token']=$token;
    header('Location: /dashboard.php');exit;
} catch(Throwable $e) {
    error_log('VideoFunds registration error: '.$e->getMessage());
    $msg=str_contains(strtolower($e->getMessage()),'email')?'email_exists':'registration_failed';
    header('Location: /index.php?error='.$msg);exit;
}
?>
