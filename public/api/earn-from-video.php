<?php
require_once __DIR__.'/../supabase.php';
header('Content-Type: application/json; charset=utf-8');
function vf_json_error(string $message,int $status=400): void {http_response_code($status);echo json_encode(['success'=>false,'message'=>$message]);exit;}
$user=sb_current_user();if(!$user){vf_json_error('Login session expired. Please sign in again.',401);}
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){vf_json_error('Method not allowed',405);}
$input=json_decode(file_get_contents('php://input'),true)?:[];
$videoId=trim((string)($input['video_youtube_id']??''));$title=trim((string)($input['video_title']??''));$seconds=(int)($input['watch_seconds']??0);
if($videoId==='')vf_json_error('Video ID is required');
try{$token=$_COOKIE['vf_access_token']??'';$r=sb_request('POST','/rest/v1/rpc/vf_claim_video_reward',['p_video_id'=>$videoId,'p_video_title'=>$title,'p_watch_seconds'=>$seconds],false,$token);
$data=$r['data'];if(is_array($data)&&isset($data[0])&&is_array($data[0]))$data=$data[0];
echo json_encode(['success'=>true,'new_balance'=>(float)($data['new_balance']??0),'reward'=>3.40,'message'=>'Earned $3.40']);
}catch(Throwable $e){$m=$e->getMessage();$status=(str_contains(strtolower($m),'not authenticated')?401:400);vf_json_error($m,$status);}
