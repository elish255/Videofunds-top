<?php
require_once __DIR__.'/../supabase.php';
const VF_ADMIN_EMAIL='mrbusiness095@gmail.com';
function h(string $s):string{return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
function csrf_token():string{$v=$_COOKIE['vf_admin_csrf']??'';if(!preg_match('/^[a-f0-9]{48}$/',$v)){$v=bin2hex(random_bytes(24));$secure=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';setcookie('vf_admin_csrf',$v,['expires'=>time()+3600,'path'=>'/admin','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);}return $v;}
function check_csrf():void{$c=$_COOKIE['vf_admin_csrf']??'';$p=(string)($_POST['csrf']??'');if($c===''||!hash_equals($c,$p))throw new RuntimeException('CSRF validation failed');}
function require_admin():void{
 $u=sb_current_user();
 if(!$u){header('Location: /admin/login.php');exit;}
 $email=strtolower(trim((string)($u['email']??'')));
 try{$p=sb_profile((string)$u['id']);}catch(Throwable $e){$p=[];}
 $emailAllowed=($email!=='' && hash_equals(strtolower(VF_ADMIN_EMAIL),$email));
 if(!$emailAllowed && empty($p['is_admin'])){http_response_code(403);exit('Admin access denied.');}
 // Keep the profile flag in sync for the owner account when a profile row exists.
 if($emailAllowed && !empty($p) && empty($p['is_admin'])){
   try{sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode((string)$u['id']),['is_admin'=>true,'updated_at'=>date('c')],true,null,['Prefer: return=minimal']);}catch(Throwable $e){error_log('Admin flag sync: '.$e->getMessage());}
 }
}
function get_user(string $uid):array{$r=sb_request('GET','/rest/v1/vf_profiles?select=*&id=eq.'.rawurlencode($uid).'&limit=1');$p=$r['data'][0]??[];if(!$p)throw new RuntimeException('User not found');return ['uid'=>$uid,'profile'=>$p];}
function list_users():array{$r=sb_request('GET','/rest/v1/vf_profiles?select=*&order=created_at.desc&limit=1000');$profiles=$r['data']??[];$counts=[];foreach($profiles as $p){$ref=trim((string)($p['referred_by']??''));if($ref!=='')$counts[$ref]=($counts[$ref]??0)+1;}return array_map(function($p)use($counts){$ref=(string)($p['referral_code']??'');return ['uid'=>$p['id'],'email'=>$p['email'],'name'=>$p['full_name'],'balance'=>$p['balance'],'referral'=>$ref,'referred_by'=>$p['referred_by'],'disabled'=>$p['banned'],'activated'=>$p['activated']??false,'membership'=>$p['membership']??'ACTIVATION','activation_payment_status'=>$p['activation_payment_status']??'unpaid','exchange_payment_status'=>$p['exchange_payment_status']??'unpaid','tax_payment_status'=>$p['tax_payment_status']??'unpaid','country'=>$p['country_code']??'TZ','created'=>$p['created_at'],'referral_count'=>$counts[$ref]??0];},$profiles);}
function list_video_library():array{$r=sb_request('GET','/rest/v1/vf_video_library?select=youtube_id,title,is_active,created_at&order=created_at.desc&limit=500');return is_array($r['data']??null)?$r['data']:[];}
function youtube_video_id_from_input(string $input):?string{
 $input=trim($input);if(preg_match('/^[A-Za-z0-9_-]{11}$/',$input))return $input;if($input==='')return null;
 if(!preg_match('/^https?:\\/\\//i',$input))$input='https://'.$input;
 $parts=parse_url($input);if(!is_array($parts))return null;$scheme=strtolower((string)($parts['scheme']??''));$host=strtolower(rtrim((string)($parts['host']??''),'.'));
 if(!in_array($scheme,['http','https'],true)||isset($parts['user'])||isset($parts['pass'])||!in_array($host,['youtube.com','www.youtube.com','m.youtube.com','music.youtube.com','youtube-nocookie.com','www.youtube-nocookie.com','youtu.be','www.youtu.be'],true))return null;
 if(isset($parts['port'])&&!in_array((int)$parts['port'],[80,443],true))return null;
 $path=(string)($parts['path']??'');$id='';
 if(in_array($host,['youtu.be','www.youtu.be'],true))$id=explode('/',trim($path,'/'))[0]??'';
 elseif($path==='/watch'){parse_str((string)($parts['query']??''),$query);$id=(string)($query['v']??'');}
 elseif(preg_match('#^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})(?:/|$)#',$path,$match))$id=$match[1];
 return preg_match('/^[A-Za-z0-9_-]{11}$/',$id)?$id:null;
}
function audit(string $action,string $uid='',array $extra=[]):void{try{sb_request('POST','/rest/v1/vf_transactions',[['user_id'=>$uid?:sb_user_id(),'type'=>'admin_credit','amount'=>0,'description'=>'ADMIN '.$action.' '.json_encode($extra),'reference'=>'AUD-'.bin2hex(random_bytes(12))]],true,null,['Prefer: return=minimal']);}catch(Throwable $e){error_log('Admin audit: '.$e->getMessage());}}
?>
