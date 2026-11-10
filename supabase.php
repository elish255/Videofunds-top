<?php
require_once __DIR__.'/supabase_config.php';
function sb_request(string $method, string $path, ?array $payload=null, bool $service=true, ?string $bearer=null, array $extraHeaders=[]): array {
    sb_require_config();
    $key = $service ? sb_service_key() : sb_anon_key();
    $headers = ['apikey: '.$key, 'Authorization: Bearer '.($bearer ?: $key), 'Accept: application/json'];
    if ($payload !== null) $headers[]='Content-Type: application/json';
    $headers=array_merge($headers,$extraHeaders);
    $ch=curl_init(sb_url().$path);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>25,CURLOPT_CONNECTTIMEOUT=>10]);
    if($payload!==null) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload,JSON_UNESCAPED_SLASHES));
    $raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
    $data=json_decode((string)$raw,true);
    if($raw===false) throw new RuntimeException('Supabase connection failed: '.$err);
    if($code>=400) throw new RuntimeException((string)($data['msg']??$data['message']??$data['error_description']??$data['error']??'Supabase request failed ('.$code.')'));
    return ['status'=>$code,'data'=>$data??[],'raw'=>$raw];
}
function sb_auth(string $path,array $payload): array { return sb_request('POST','/auth/v1/'.$path,$payload,false,null); }
function sb_set_auth_cookies(array $auth): void {
    $secure=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';
    $opts=['expires'=>time()+(int)($auth['expires_in']??3600),'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax'];
    setcookie('vf_access_token',(string)$auth['access_token'],$opts);
    $opts['expires']=time()+60*60*24*30;setcookie('vf_refresh_token',(string)($auth['refresh_token']??''),$opts);
}
function sb_clear_auth_cookies(): void { foreach(['vf_access_token','vf_refresh_token'] as $n)setcookie($n,'',['expires'=>1,'path'=>'/','httponly'=>true,'samesite'=>'Lax']); }
function sb_current_user(): ?array {
    static $user=false;if($user!==false)return $user;
    $token=$_COOKIE['vf_access_token']??'';if($token==='')return $user=null;
    try{$r=sb_request('GET','/auth/v1/user',null,false,$token);$user=$r['data'];return $user;}
    catch(Throwable $e){$refresh=$_COOKIE['vf_refresh_token']??'';if($refresh!==''){try{$a=sb_auth('token?grant_type=refresh_token',['refresh_token'=>$refresh]);sb_set_auth_cookies($a);$user=$a['user']??null;return $user;}catch(Throwable $ignored){}}
        sb_clear_auth_cookies();return $user=null;}
}
function sb_require_user(): array {$u=sb_current_user();if(!$u){header('Location: /index.php');exit;}return $u;}
function sb_profile(string $uid): array {$r=sb_request('GET','/rest/v1/profiles?select=*&id=eq.'.rawurlencode($uid).'&limit=1');return $r['data'][0]??[];}
function sb_user_id(): string {$u=sb_require_user();return (string)$u['id'];}
function sb_rpc(string $name,array $args): array {$r=sb_request('POST','/rest/v1/rpc/'.$name,$args,true,null,['Prefer: return=representation']);return $r['data'];}
