<?php
require_once __DIR__.'/supabase.php';$token=$_COOKIE['vf_access_token']??'';if($token!==''){try{sb_request('POST','/auth/v1/logout',null,false,$token);}catch(Throwable $e){}}
sb_clear_auth_cookies();header('Location: /index.php');exit;
