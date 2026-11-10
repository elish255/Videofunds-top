<?php
require_once __DIR__.'/../supabase.php';require_once __DIR__.'/_lib.php';require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
try{check_csrf();$action=$_POST['action']??'';$uid=trim($_POST['uid']??'');
 if(in_array($action,['ban','unban'],true)){if($uid==='')throw new RuntimeException('UID inahitajika');sb_request('PATCH','/rest/v1/profiles?id=eq.'.rawurlencode($uid),['banned'=>$action==='ban'],true,null,['Prefer: return=minimal']);audit($action,$uid);header('Location: /admin/user.php?uid='.rawurlencode($uid));exit;}
 if($action==='notify_all'||$action==='notify_one'){$title=trim($_POST['title']??'');$body=trim($_POST['body']??'');if($title===''||$body==='')throw new RuntimeException('Kichwa na ujumbe vinahitajika');$rows=[];if($action==='notify_one'){if($uid==='')throw new RuntimeException('UID inahitajika');$rows[]=['user_id'=>$uid,'title'=>$title,'body'=>$body];}else{$users=sb_request('GET','/rest/v1/profiles?select=id&limit=1000')['data'];foreach($users as $u)$rows[]=['user_id'=>$u['id'],'title'=>$title,'body'=>$body];}if($rows)sb_request('POST','/rest/v1/notifications',$rows,true,null,['Prefer: return=minimal']);header('Location: /admin/?sent=1');exit;}
 throw new RuntimeException('Action haijulikani');
}catch(Throwable $e){http_response_code(400);echo '<p>'.h($e->getMessage()).'</p><a href="/admin/">Rudi</a>';}
