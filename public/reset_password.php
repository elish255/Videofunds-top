<?php
require_once __DIR__.'/supabase.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $email=trim($_POST['email']??'');
 if(filter_var($email,FILTER_VALIDATE_EMAIL)){
  try{sb_auth('recover',['email'=>$email,'options'=>['emailRedirectTo'=>sb_url().'/index.php?reset=1']]);}catch(Throwable $e){error_log('Password recovery: '.$e->getMessage());}
 }
 header('Location: index.php?reset_sent=1');exit;
}
header('Location: index.php');exit;
