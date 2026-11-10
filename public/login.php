<?php
require_once __DIR__.'/supabase.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
$email=trim($_POST['email']??'');$password=(string)($_POST['password']??'');
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||$password===''){header('Location: index.php?error=invalid_input');exit;}
try{$a=sb_auth('token?grant_type=password',['email'=>$email,'password'=>$password]);sb_ensure_profile($a['user']??[] ,['email'=>$email]);sb_set_auth_cookies($a);header('Location: dashboard.php');exit;}
catch(Throwable $e){error_log('Supabase login: '.$e->getMessage());header('Location: index.php?error=invalid_login');exit;}
