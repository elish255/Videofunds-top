<?php
require_once __DIR__.'/supabase.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
$name=trim($_POST['fullname']??'');$email=trim($_POST['email']??'');$password=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm_password']??'');$ref=trim($_POST['referral_code']??'');$country=trim($_POST['country_code']??'TZ');$currency=trim($_POST['currency_code']??'USD');$rate=(float)($_POST['exchange_rate']??1);
if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8||$password!==$confirm){header('Location: index.php?error=invalid_registration');exit;}
try{$a=sb_auth('signup',['email'=>$email,'password'=>$password,'data'=>['full_name'=>$name]]);$uid=$a['user']['id']??'';if($uid==='')throw new RuntimeException('Supabase signup did not return a user.');
    sb_request('POST','/rest/v1/vf_profiles',['id'=>$uid,'full_name'=>$name,'email'=>$email,'balance'=>5.00,'referral_code'=>strtoupper(substr(bin2hex(random_bytes(5)),0,8)),'referred_by'=>$ref?:null,'country_code'=>$country,'currency_code'=>$currency,'exchange_rate'=>$rate,'daily_watch_count'=>0,'last_watch_date'=>date('Y-m-d')],true,null,['Prefer: resolution=merge-duplicates,return=minimal']);
    // Email confirmation should be disabled in Supabase Auth settings.
    // If signup did not return a session, immediately try password login.
    if (empty($a['access_token'])) {
        $a = sb_auth('token?grant_type=password', [
            'email' => $email,
            'password' => $password
        ]);
    }

    if (empty($a['access_token']) || empty($a['user']['id'])) {
        throw new RuntimeException('Signup succeeded but no login session was returned. Disable email confirmation in Supabase Authentication settings.');
    }

    sb_set_auth_cookies($a);
    header('Location: dashboard.php');
    exit;
}catch(Throwable $e){error_log('Supabase registration: '.$e->getMessage());header('Location: index.php?error=registration_failed');exit;}
