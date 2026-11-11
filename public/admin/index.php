<?php
require_once __DIR__ . ('/admin' === substr(__DIR__, -6) ? '/../runtime_bootstrap.php' : '/runtime_bootstrap.php');
require_once __DIR__.'/_lib.php';
require_admin();

$error=''; $users=[];$videoLibrary=[];$videoLibraryError='';
try { $users=list_users(); } catch(Throwable $e) { $error=$e->getMessage(); }
try { $videoLibrary=list_video_library(); } catch(Throwable $e) { $videoLibraryError=$e->getMessage(); }
$allUsers=$users;
$q=strtolower(trim($_GET['q']??''));
if($q!==''){
    $users=array_values(array_filter($users, function($u) use($q){
        return str_contains(strtolower(($u['name']??'').' '.($u['email']??'').' '.($u['uid']??'').' '.($u['referral']??'')),$q);
    }));
}
$manualRequests=[];$manualRequestError='';
try{$mr=sb_request('GET','/rest/v1/vf_manual_payment_requests?select=*&order=created_at.desc&limit=200');$manualRequests=$mr['data']??[];}catch(Throwable $e){$manualRequestError=$e->getMessage();}
$pendingManual=count(array_filter($manualRequests,fn($r)=>($r['status']??'')==='pending'));
$withdrawals=[];$withdrawalError='';
try{$wr=sb_request('GET','/rest/v1/vf_withdrawals?select=*&order=created_at.desc&limit=300');$withdrawals=$wr['data']??[];}catch(Throwable $e){$withdrawalError=$e->getMessage();}
$openWithdrawals=array_values(array_filter($withdrawals,fn($w)=>in_array(($w['status']??''),['pending','approved'],true)));
$doneWithdrawals=array_slice(array_values(array_filter($withdrawals,fn($w)=>in_array(($w['status']??''),['paid','rejected'],true))),0,20);
$pendingWithdrawals=count(array_filter($withdrawals,fn($w)=>($w['status']??'')==='pending'));
$csrf=csrf_token();
$total=count($users);
$banned=count(array_filter($users,fn($u)=>!empty($u['disabled'])));

$totalReferral=count(array_filter($users,fn($u)=>trim((string)($u['referred_by']??''))!==''));
$activeReferral=count(array_filter($users,fn($u)=>trim((string)($u['referred_by']??''))!=='' && empty($u['disabled'])));
$totalBalance=array_sum(array_map(fn($u)=>(float)$u['balance'],$users));
$flash='';
if(isset($_GET['sent'])) $flash='Ujumbe umetumwa kwa mafanikio.';
if(isset($_GET['updated'])) $flash='Mabadiliko yamehifadhiwa.';
if(isset($_GET['deleted'])) $flash='Akaunti imefutwa.';
if(isset($_GET['video_added'])) $flash='Video imeongezwa na sasa inaonekana kwa watumiaji.';
if(isset($_GET['video_updated'])) $flash='Hali ya video imebadilishwa.';
?>
<!doctype html>
<html lang="sw">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gochati — VideoFunds Admin</title>
<style>
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;background:#06101d;color:#edf4fb}
body{overflow-x:hidden}.app{min-height:100vh}.sidebar{position:fixed;left:0;top:0;bottom:0;width:310px;background:#1d6f5a;color:#fff;padding:28px 18px;overflow-y:auto;z-index:1000;transition:transform .25s ease}.brand{display:flex;align-items:center;gap:13px;padding:0 12px 30px}.logo{width:50px;height:50px;border-radius:50%;background:#06101d;display:grid;place-items:center;font-weight:900;font-size:17px;color:#17d1e0;border:3px solid #19bca9}.brand b{font-size:25px;line-height:1}.brand small{display:block;color:#d6f4ec;margin-top:6px;font-size:14px}.close{display:none;position:absolute;right:18px;top:18px;width:34px;height:34px;border-radius:50%;border:2px solid #19cfc0;background:transparent;color:#fff;font-size:20px}.section-title{font-size:12px;letter-spacing:3px;font-weight:800;color:#c3e8df;margin:18px 22px 10px}.nav{display:flex;flex-direction:column;gap:3px}.nav a{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 20px;border-radius:28px;color:#fff;text-decoration:none;font-weight:700}.nav a:hover,.nav a.active{background:rgba(255,255,255,.14)}.nav .left{display:flex;align-items:center;gap:13px}.ico{width:23px;text-align:center;font-size:20px}.count{min-width:36px;padding:7px 10px;border-radius:18px;background:rgba(255,255,255,.14);text-align:center;font-size:13px}.main{margin-left:310px;min-height:100vh;background:radial-gradient(circle at 80% 0,#10223b 0,#06101d 38%);padding:22px 28px 50px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:22px}.menu{display:none;background:#1d6f5a;color:#fff;border:0;border-radius:12px;padding:11px 14px;font-size:20px}.topbar h1{font-size:24px;margin:0}.topbar .right{display:flex;align-items:center;gap:10px}.logout{background:#0e1a2a;border:1px solid #25384f;color:#fff;text-decoration:none;padding:10px 15px;border-radius:12px}.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.stat{background:#0d1a29;border:1px solid #1c2c40;border-radius:18px;padding:18px}.stat .label{color:#9fb0c2;font-size:13px}.stat .num{font-size:27px;font-weight:900;margin-top:6px}.stat .sub{font-size:12px;color:#6fd8c3;margin-top:4px}.panel{background:#0c1827;border:1px solid #1d2d42;border-radius:22px;padding:18px;margin-bottom:18px;box-shadow:0 15px 40px rgba(0,0,0,.16)}.panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}.panel h2{margin:0;font-size:20px}.search{display:flex;gap:9px;flex-wrap:wrap}.search input{min-width:230px;flex:1}.input,.search input{background:#07111e;border:1px solid #26394f;color:#eef5fb;border-radius:12px;padding:12px 13px;outline:none}.btn{border:0;border-radius:12px;padding:11px 15px;color:#fff;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:7px}.btn-blue{background:#1767c5}.btn-green{background:#08b9ba;color:#04222a}.btn-red{background:#c73545}.btn-dark{background:#07111e;border:1px solid #26394f}.btn-yellow{background:#d49718;color:#111}.flash{background:#0b553f;border:1px solid #1dbb88;color:#d9fff2;padding:12px 14px;border-radius:12px;margin-bottom:15px}.err{background:#5b2530;border:1px solid #bd4b5a;padding:12px 14px;border-radius:12px;margin-bottom:15px}.toolbar{display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:15px}.bulk{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.user-list{display:grid;gap:14px}.user-card{background:#101e2d;border:1px solid #24364b;border-radius:22px;padding:17px}.user-top{display:flex;gap:12px;align-items:flex-start}.check{width:21px;height:21px;margin-top:3px}.user-main{flex:1;min-width:0}.user-name{font-size:20px;font-weight:900;margin:0 0 5px}.meta{color:#8799ad;font-size:13px;line-height:1.65}.status-line{margin-top:6px;color:#aebdcc;font-size:13px}.badge{display:inline-block;padding:4px 9px;border-radius:15px;font-size:11px;font-weight:900;margin-left:4px}.active{background:#123e32;color:#62e1bc}.banned{background:#4b2027;color:#ff8793}.activation{background:#253244;color:#c8d4df}.exchange{background:#4d3a0b;color:#ffd76a}.tax{background:#3c214d;color:#e5a7ff}.balance-row{display:grid;grid-template-columns:minmax(140px,1fr) auto auto;gap:9px;margin-top:14px}.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:13px;padding-top:13px;border-top:1px solid #213146}.actions form{margin:0}.small{font-size:12px}.empty{padding:28px;text-align:center;color:#8091a5}.drawer-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.62);z-index:900}.forms{display:grid;grid-template-columns:1fr 1fr;gap:14px}.form-card{background:#101e2d;border:1px solid #24364b;border-radius:17px;padding:15px}.form-card label{display:block;color:#aebdcc;font-size:13px;font-weight:700;margin:0 0 6px}.form-card input,.form-card textarea{width:100%;margin-bottom:10px}.form-card textarea{min-height:100px;resize:vertical}.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1200;place-items:center;padding:18px}.modal.show{display:grid}.modal-card{width:min(520px,100%);background:#101e2d;border:1px solid #2a3c53;border-radius:20px;padding:20px}.modal-card h3{margin-top:0}.modal-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:14px}@media(max-width:1050px){.stats{grid-template-columns:repeat(2,1fr)}.sidebar{width:280px}.main{margin-left:280px}}@media(max-width:780px){.sidebar{transform:translateX(-100%);width:min(310px,88vw)}.sidebar.open{transform:translateX(0)}.close{display:block}.drawer-backdrop.show{display:block}.main{margin-left:0;padding:16px 12px 40px}.menu{display:block}.topbar h1{font-size:19px}.stats{grid-template-columns:repeat(2,1fr);gap:9px}.stat{padding:14px}.stat .num{font-size:22px}.forms{grid-template-columns:1fr}.panel{padding:14px}.balance-row{grid-template-columns:1fr 1fr 1fr}.balance-row .input{grid-column:1/-1}.actions .btn{flex:1;min-width:120px}.user-name{font-size:18px}}@media(max-width:480px){.stats{grid-template-columns:1fr 1fr}.search input{min-width:100%}.balance-row{grid-template-columns:1fr 1fr}.balance-row .input{grid-column:1/-1}.actions .btn{min-width:calc(50% - 8px)}.topbar .logout{display:none}}
.video-manager{display:grid;gap:15px}.video-manager-intro{margin:0;color:#91a4b8;line-height:1.55}.video-form{display:grid;grid-template-columns:minmax(180px,1fr) minmax(240px,1.5fr) auto;align-items:end;gap:12px;padding:16px;border:1px solid #22364c;background:#091522;border-radius:16px}.video-form label{display:grid;gap:7px;color:#b7c6d5;font-size:13px;font-weight:700}.video-form .input{width:100%;min-width:0}.video-library{display:grid;gap:10px}.video-entry{display:grid;grid-template-columns:minmax(0,1fr) auto auto;align-items:center;gap:14px;padding:15px;border:1px solid #22364c;background:#091522;border-radius:14px}.video-entry-info{min-width:0}.video-entry-title{font-weight:750;overflow-wrap:anywhere}.video-entry-meta{color:#8295aa;font-size:12px;margin-top:4px}.video-entry-link{display:inline-block;color:#51d9c1;font-size:12px;margin-top:5px}.video-entry form{margin:0}.video-entry .btn{white-space:nowrap}@media(max-width:760px){.video-form{grid-template-columns:1fr}.video-entry{grid-template-columns:minmax(0,1fr) auto}.video-entry form{grid-column:1/-1}.video-entry form .btn{width:100%}}@media(max-width:480px){.video-entry{grid-template-columns:1fr}.video-entry form{grid-column:auto}.video-entry .badge{justify-self:start}}
</style>
</head>
<body>
<div class="app">
<aside class="sidebar" id="sidebar">
<button class="close" onclick="closeMenu()">×</button>
<div class="brand"><div class="logo">VF</div><div><b>Gochati</b><small>Admin Control</small></div></div>
<div class="section-title">MWANZO</div>
<nav class="nav"><a href="/admin/" class="active"><span class="left"><span class="ico">▦</span>Dashboard</span></a><a href="#videos"><span class="left"><span class="ico">▶</span>Video links</span><span class="count"><?=count($videoLibrary)?></span></a></nav>
<div class="section-title">WATUMIAJI</div>
<nav class="nav">
<a href="#users"><span class="left"><span class="ico">♧</span>Total Referral</span><span class="count"><?=$totalReferral?></span></a>
<a href="#users"><span class="left"><span class="ico">♙</span>Active Referral</span><span class="count"><?=$activeReferral?></span></a>
<a href="#users"><span class="left"><span class="ico">♙</span>Inactive Referral</span><span class="count"><?=max(0,$totalReferral-$activeReferral)?></span></a>
<a href="#users"><span class="left"><span class="ico">✓</span>Make Active</span><span class="count"><?=count(array_filter($users,fn($u)=>!empty($u['activated'])))?></span></a>
<a href="#users"><span class="left"><span class="ico">♛</span>Membership: Activation / Exchange / Tax</span></a>
<a href="#manual-payments"><span class="left"><span class="ico">✓</span>LIPA NAMBA approvals</span><span class="count"><?=$pendingManual?></span></a>
<a href="#withdrawals"><span class="left"><span class="ico">⇩</span>Withdrawals</span><span class="count"><?=$pendingWithdrawals?></span></a>
<a href="#balance"><span class="left"><span class="ico">⊕</span>Add / Reduce Amount</span></a>
<a href="#users"><span class="left"><span class="ico">⊘</span>Ban Account</span><span class="count"><?=$banned?></span></a>
<a href="#users"><span class="left"><span class="ico">▢</span>Delete Account</span></a>
</nav>
<div class="section-title">HUDUMA</div>
<nav class="nav">
<a href="#notifications"><span class="left"><span class="ico">▢</span>Customer care</span></a>
<a href="#users"><span class="left"><span class="ico">⊘</span>Namba zilizozuiwa</span><span class="count"><?=$banned?></span></a>
<a href="#balance"><span class="left"><span class="ico">▤</span>Malipo</span></a>
<a href="#notifications"><span class="left"><span class="ico">♧</span>Notifications</span></a>
<a href="#sms"><span class="left"><span class="ico">□</span>SMS</span></a>
</nav>
</aside>
<div class="drawer-backdrop" id="backdrop" onclick="closeMenu()"></div>
<main class="main">
<div class="topbar"><div style="display:flex;align-items:center;gap:12px"><button class="menu" onclick="openMenu()">☰</button><div><h1>Admin Control</h1><div class="small" style="color:#8295aa"><?=h(VF_ADMIN_EMAIL ?: 'VideoFunds administrator')?></div></div></div><div class="right"><button type="button" class="btn btn-yellow" id="enableAdminAlerts">🔔 Washa arifa + sauti</button><a class="logout" href="/admin/logout.php">Logout</a></div></div>
<div id="adminAlertToast" style="display:none;position:sticky;top:10px;z-index:1500;background:#123e32;border:1px solid #36d6a5;color:#eafff7;padding:15px 18px;border-radius:14px;margin-bottom:15px;box-shadow:0 12px 35px #0008"><b id="adminAlertTitle">Ombi jipya la malipo!</b><div id="adminAlertText" style="margin:5px 0 10px"></div><a href="/admin/?refresh=1#manual-payments" class="btn btn-green">Fungua maombi ya malipo</a></div><?php if($flash):?><div class="flash">✓ <?=h($flash)?></div><?php endif?>
<?php if($error):?><div class="err"><?=h($error)?></div><?php endif?>
<section class="stats">
<div class="stat"><div class="label">Watumiaji</div><div class="num"><?=$total?></div><div class="sub">Accounts zote</div></div>
<div class="stat"><div class="label">Active users</div><div class="num"><?=$total-$banned?></div><div class="sub">Accounts zinazofanya kazi</div></div>
<div class="stat"><div class="label">Flow Payments</div><div class="num"><?=count(array_filter($users,fn($u)=>(($u['exchange_payment_status']??'')==='paid')))?></div><div class="sub">Exchange paid</div></div>
<div class="stat"><div class="label">Balance jumla</div><div class="num">TSh <?=number_format($totalBalance*2600,0)?></div><div class="sub">$<?=number_format($totalBalance,2)?></div></div>
</section>
<section class="panel" id="videos">
<div class="panel-head"><h2>Video links</h2><span class="small" style="color:#8295aa"><?=count($videoLibrary)?> managed</span></div>
<div class="video-manager">
<p class="video-manager-intro">Ongeza kiungo au video ID ya YouTube. Video mpya huonekana kwenye ukurasa wa kutazama kwa watumiaji; unaweza pia kuzima na kuiwasha tena bila kuifuta.</p>
<?php if($videoLibraryError):?><div class="err">Video library haikupatikana: <?=h($videoLibraryError)?></div><?php endif?>
<form class="video-form" method="post" action="/admin/action.php">
<input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="video_add">
<label for="video-title">Title<input class="input" id="video-title" name="title" maxlength="150" required placeholder="Mfano: Safari ya baharini"></label>
<label for="video-url">YouTube URL au ID<input class="input" id="video-url" name="video_url" type="text" inputmode="url" maxlength="2048" required placeholder="https://youtu.be/… au video ID"></label>
<button class="btn btn-green" type="submit">Ongeza video</button>
</form>
<div class="video-library">
<?php if(!$videoLibrary):?><div class="empty">Bado hakuna viungo vilivyoongezwa na admin.</div><?php endif?>
<?php foreach($videoLibrary as $video):$videoId=(string)($video['youtube_id']??'');$videoActive=!empty($video['is_active']);?>
<article class="video-entry">
<div class="video-entry-info"><div class="video-entry-title"><?=h((string)($video['title']??''))?></div><div class="video-entry-meta">ID: <?=h($videoId)?> · <?=h(date('d/m/Y H:i',strtotime((string)($video['created_at']??'now'))))?></div><a class="video-entry-link" href="https://youtu.be/<?=rawurlencode($videoId)?>" target="_blank" rel="noopener noreferrer">Fungua YouTube</a></div>
<span class="badge <?=$videoActive?'active':'banned'?>"><?=$videoActive?'ACTIVE':'PAUSED'?></span>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="video_toggle"><input type="hidden" name="youtube_id" value="<?=h($videoId)?>"><input type="hidden" name="is_active" value="<?=$videoActive?'0':'1'?>"><button class="btn <?=$videoActive?'btn-red':'btn-green'?>" type="submit"><?=$videoActive?'Zima video':'Washa video'?></button></form>
</article>
<?php endforeach?>
</div>
</div>
</section>
<section class="panel" id="manual-payments">
<div class="panel-head"><h2>Uthibitisho wa malipo ya LIPA NAMBA</h2><span class="small" style="color:#8295aa"><?=$pendingManual?> yanayosubiri</span></div>
<?php if($manualRequestError):?><div class="err">Jedwali la uthibitisho halijapatikana. Tekeleza SQL migration ya <b>vf_manual_payment_requests</b> kwanza.</div><?php endif?>
<?php $pendingRows=array_values(array_filter($manualRequests,fn($r)=>($r['status']??'')==='pending')); ?>
<?php if(!$pendingRows):?><div class="empty">Hakuna ombi jipya la LIPA NAMBA linalosubiri.</div><?php endif?>
<div class="user-list">
<?php foreach($pendingRows as $mr):$mpUser=null;foreach($allUsers as $candidate){if(($candidate['uid']??'')===($mr['user_id']??'')){$mpUser=$candidate;break;}}?>
<article class="user-card"><div class="user-top"><div class="user-main"><h3 class="user-name"><?=h((string)($mpUser['name']??'User'))?></h3><div class="meta"><?=h((string)($mpUser['email']??''))?> · Simu: <b><?=h((string)$mr['phone'])?></b></div><div class="status-line">Hatua: <span class="badge activation"><?=h(strtoupper((string)$mr['stage']))?></span> · Kiasi: <b>TZS <?=number_format((float)$mr['amount'])?></b> · Tarehe: <?=h(date('d/m/Y H:i',strtotime((string)$mr['created_at'])))?></div><div class="small" style="color:#f5c56b;margin-top:7px">Hakiki muamala kwenye akaunti ya LIPA NAMBA kabla ya ku-approve.</div></div></div>
<div class="actions">
<form method="post" action="/admin/action.php" onsubmit="return confirm('Umethibitisha kupokea TZS <?=number_format((float)$mr['amount'])?> kutoka <?=h((string)$mr['phone'])?>?')"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="manual_payment_approve"><input type="hidden" name="request_id" value="<?=h((string)$mr['id'])?>"><button class="btn btn-green">APPROVE — Ruhusu hatua inayofuata</button></form>
<form method="post" action="/admin/action.php" onsubmit="return confirm('Ukireject, user atarudishwa kulipia hatua hii tena. Endelea?')"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="manual_payment_reject"><input type="hidden" name="request_id" value="<?=h((string)$mr['id'])?>"><button class="btn btn-red">REJECT — Rudisha alipe tena</button></form>
</div></article><?php endforeach?>
</div></section>
<section class="panel" id="withdrawals">
<div class="panel-head"><h2>Withdrawals (Pending → Approved → Paid)</h2><span class="small" style="color:#8295aa"><?=$pendingWithdrawals?> pending · <?=count($openWithdrawals)?> wazi</span></div>
<?php if($withdrawalError):?><div class="err">Jedwali la withdrawals halijapatikana. Tekeleza SQL migration ya <b>supabase-withdrawal-flow-migration.sql</b> kwanza.</div><?php endif?>
<?php if(!$openWithdrawals):?><div class="empty">Hakuna withdrawal inayosubiri.</div><?php endif?>
<div class="user-list">
<?php foreach($openWithdrawals as $w):$wUser=null;foreach($allUsers as $candidate){if(($candidate['uid']??'')===($w['user_id']??'')){$wUser=$candidate;break;}}$wst=strtolower((string)($w['status']??'pending'));$wm=(string)($w['method']??'phone');$wmLabel=['phone'=>'Simu','visa'=>'Visa','paypal'=>'PayPal'][$wm]??$wm;?>
<article class="user-card"><div class="user-top"><div class="user-main"><h3 class="user-name"><?=h((string)($wUser['name']??'User'))?> <span class="badge <?=$wst==='approved'?'exchange':'activation'?>"><?=h(strtoupper($wst))?></span></h3>
<div class="meta"><?=h((string)($wUser['email']??''))?> · <?=h($wmLabel)?>: <b><?=h((string)($w['account_number']??''))?></b></div>
<div class="status-line">Kiasi: <b>$<?=number_format((float)$w['amount'],2)?></b> <span style="color:#8295aa">(TZS <?=number_format((float)$w['amount']*2600,0)?>)</span> · Tarehe: <?=h(date('d/m/Y H:i',strtotime((string)$w['created_at'])))?> · #<?=h((string)$w['id'])?></div></div></div>
<div class="actions">
<?php if($wst==='pending'):?><form method="post" action="/admin/action.php" onsubmit="return confirm('Kubali withdrawal hii ya $<?=number_format((float)$w['amount'],2)?>?')"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="withdrawal_set"><input type="hidden" name="withdrawal_id" value="<?=h((string)$w['id'])?>"><input type="hidden" name="status" value="approved"><button class="btn btn-blue">APPROVE</button></form><?php endif?>
<form method="post" action="/admin/action.php" onsubmit="return confirm('Umeshalipa $<?=number_format((float)$w['amount'],2)?> kwa user huyu? Status itakuwa PAID.')"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="withdrawal_set"><input type="hidden" name="withdrawal_id" value="<?=h((string)$w['id'])?>"><input type="hidden" name="status" value="paid"><button class="btn btn-green">MARK AS PAID</button></form>
<form method="post" action="/admin/action.php" onsubmit="return confirm('Ukireject, $<?=number_format((float)$w['amount'],2)?> itarudishwa kwenye balance ya user. Endelea?')"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="withdrawal_set"><input type="hidden" name="withdrawal_id" value="<?=h((string)$w['id'])?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-red">REJECT + REFUND</button></form>
</div></article><?php endforeach?>
</div>
<?php if($doneWithdrawals):?><h3 style="font-size:15px;margin:18px 0 8px;color:#9fb0c2">Zilizokamilika hivi karibuni</h3>
<div class="meta"><?php foreach($doneWithdrawals as $w):$wUser=null;foreach($allUsers as $candidate){if(($candidate['uid']??'')===($w['user_id']??'')){$wUser=$candidate;break;}}?>
<div>#<?=h((string)$w['id'])?> · <?=h((string)($wUser['name']??'User'))?> · $<?=number_format((float)$w['amount'],2)?> · <span class="badge <?=($w['status']??'')==='paid'?'active':'banned'?>"><?=h(strtoupper((string)$w['status']))?></span> · <?=h(date('d/m/Y H:i',strtotime((string)($w['updated_at']??$w['created_at']))))?></div>
<?php endforeach?></div><?php endif?>
</section>
<section class="panel" id="users">
<div class="panel-head"><h2>Watumiaji</h2><span class="small" style="color:#8295aa"><?=count($users)?> wanaoonekana</span></div>
<form class="search" method="get"><input class="input" name="q" placeholder="Tafuta jina, email, UID au referral code" value="<?=h($q)?>"><button class="btn btn-blue">Tafuta</button><?php if($q!==''):?><a class="btn btn-dark" href="/admin/">Safisha</a><?php endif?></form>
<div class="toolbar" style="margin-top:15px"><div class="bulk"><label class="small"><input type="checkbox" id="selectAll"> Chagua wote</label><button type="button" class="btn btn-red" onclick="bulkDelete()">Delete selected</button></div><span class="small" style="color:#8091a5">Actions zote zinalindwa na CSRF.</span></div>
<div class="user-list">
<?php foreach($users as $u):
$membership=strtoupper((string)($u['membership']??'ACTIVATION')); if(!in_array($membership,['ACTIVATION','EXCHANGE','TAX'],true))$membership='ACTIVATION';
?>
<article class="user-card">
<div class="user-top"><input class="check user-check" type="checkbox" value="<?=h($u['uid'])?>">
<div class="user-main"><h3 class="user-name"><?=h($u['name']?:'Unnamed user')?> <span style="color:#8397ab;font-size:14px;font-weight:600">@<?=h(substr((string)$u['referral'],0,10))?></span></h3>
<div class="meta"><?=h($u['email'])?> · <?=h($u['country']??'TZ')?> · Usajili: <?=h(date('d/m/Y, H:i',strtotime((string)$u['created'])))?></div>
<div class="status-line">Hali: <span class="badge <?=!empty($u['disabled'])?'banned':'active'?>"><?=!empty($u['disabled'])?'BANNED':'ACTIVE'?></span>
<span class="badge <?=strtolower($membership)?>"><?=h($membership)?></span> · Walioalikwa: <?=h((string)($u['referral_count']??0))?> · Balance: <b>TZS <?=number_format((float)$u['balance']*2600,0)?></b> <span style="color:#8295aa">($<?=number_format((float)$u['balance'],2)?>)</span></div></div></div>
<div class="balance-row"><input class="input amount-input" type="number" min="0.01" step="0.01" placeholder="Kiasi (USD)" data-uid="<?=h($u['uid'])?>">
<button type="button" class="btn btn-green" onclick="balanceAction('credit','<?=h($u['uid'])?>',this)">+ Ongeza balance</button>
<button type="button" class="btn btn-dark" onclick="balanceAction('debit','<?=h($u['uid'])?>',this)">− Punguza balance</button></div>
<div class="actions">
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="make_active"><button class="btn btn-green" onclick="return confirm('Umethibitisha malipo ya Activation?')">Thibitisha Activation</button></form>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="remove_activation"><button class="btn btn-red" onclick="return confirm('Uondoe Activation pamoja na hatua zote zinazofuata kwenye account hii?')">Ondoa Activation</button></form>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="exchange_paid"><button class="btn btn-dark" onclick="return confirm('Umethibitisha malipo ya Exchange?')">Thibitisha Exchange</button></form>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="remove_exchange"><button class="btn btn-red" onclick="return confirm('Uondoe Exchange pamoja na Tax kwenye account hii?')">Ondoa Exchange</button></form>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="tax_paid"><button class="btn btn-yellow" onclick="return confirm('Umethibitisha malipo ya Tax?')">Thibitisha Tax</button></form>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="remove_tax"><button class="btn btn-red" onclick="return confirm('Uondoe Tax kwenye account hii?')">Ondoa Tax</button></form>
<form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="uid" value="<?=h($u['uid'])?>"><input type="hidden" name="action" value="<?=!empty($u['disabled'])?'unban':'ban'?>"><button class="btn <?=!empty($u['disabled'])?'btn-green':'btn-red'?>"><?=!empty($u['disabled'])?'Unban':'Ban'?></button></form>
<button type="button" class="btn btn-dark" onclick="openNotify('<?=h($u['uid'])?>')">▢ Notify user</button>
<button type="button" class="btn btn-dark" onclick="openSms('<?=h($u['name'])?>')">□ Tuma SMS</button>
<a class="btn btn-blue" href="/admin/user.php?uid=<?=rawurlencode($u['uid'])?>">Fungua</a>
</div></article>
<?php endforeach?>
<?php if(!$users):?><div class="empty">Hakuna user aliyepatikana.</div><?php endif?>
</div></section>
<section class="panel" id="balance"><div class="panel-head"><h2>Add / Reduce Amount</h2><span class="small" style="color:#8295aa">Badilisha balance kwa USD</span></div><div class="form-card"><p class="small" style="color:#9fb0c2;margin-top:0">Tumia input ya kila account hapo juu. Kila mabadiliko huandikwa pia kwenye transaction history kama <b>admin_credit</b> au <b>admin_debit</b>.</p><a class="btn btn-green" href="#users">Rudi kwenye accounts</a></div></section>
<section class="panel" id="notifications"><div class="panel-head"><h2>Notifications</h2></div><div class="forms">
<form class="form-card" method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="notify_all"><label>Kichwa</label><input class="input" name="title" required maxlength="100"><label>Ujumbe</label><textarea class="input" name="body" required maxlength="1000"></textarea><button class="btn btn-green">Tuma kwa users wote</button></form>
<form class="form-card" method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="notify_one"><label>UID ya user</label><input class="input" id="notifyUid" name="uid" required><label>Kichwa</label><input class="input" id="notifyTitle" name="title" required maxlength="100"><label>Ujumbe</label><textarea class="input" name="body" required maxlength="1000"></textarea><button class="btn btn-blue">Tuma kwa user mmoja</button></form>
</div></section>
<section class="panel" id="sms"><div class="panel-head"><h2>SMS</h2></div><div class="form-card"><p class="small" style="color:#9fb0c2">Tuma SMS kupitia app ya SMS ya simu yako bila SMS gateway.</p><button class="btn btn-blue" type="button" onclick="openSms('')">Fungua SMS composer</button></div></section>
</main></div>
<div class="modal" id="notifyModal"><div class="modal-card"><h3>Tuma notification</h3><form method="post" action="/admin/action.php"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="action" value="notify_one"><label class="small">UID</label><input class="input" id="modalUid" name="uid" required><label class="small">Kichwa</label><input class="input" name="title" required maxlength="100"><label class="small">Ujumbe</label><textarea class="input" name="body" required maxlength="1000"></textarea><div class="modal-actions"><button type="button" class="btn btn-dark" onclick="closeModal('notifyModal')">Cancel</button><button class="btn btn-green">Tuma</button></div></form></div></div>
<div class="modal" id="smsModal"><div class="modal-card"><h3>Tuma SMS</h3><p class="small" id="smsFor" style="color:#8fa1b4"></p><input class="input" id="smsNumber" placeholder="Namba ya simu, mfano 2557XXXXXXXX"><textarea class="input" id="smsBody" placeholder="Andika ujumbe wa SMS"></textarea><div class="modal-actions"><button type="button" class="btn btn-dark" onclick="closeModal('smsModal')">Cancel</button><button type="button" class="btn btn-green" onclick="sendSms()">Fungua SMS</button></div></div></div>
<script>
function openMenu(){document.getElementById('sidebar').classList.add('open');document.getElementById('backdrop').classList.add('show')}
function closeMenu(){document.getElementById('sidebar').classList.remove('open');document.getElementById('backdrop').classList.remove('show')}
function closeModal(id){document.getElementById(id).classList.remove('show')}
function openNotify(uid){document.getElementById('modalUid').value=uid;document.getElementById('notifyModal').classList.add('show')}
function openSms(name){document.getElementById('smsFor').textContent=name?('Kwa: '+name):'Ingiza namba ya simu';document.getElementById('smsModal').classList.add('show')}
function sendSms(){const n=document.getElementById('smsNumber').value.trim(),b=document.getElementById('smsBody').value.trim();if(!n||!b){alert('Weka namba na ujumbe kwanza.');return}location.href='sms:'+encodeURIComponent(n)+'?body='+encodeURIComponent(b)}

const adminFeedStart = <?=json_encode(gmdate('Y-m-d\\TH:i:s\\Z'))?>;
const alertButton=document.getElementById('enableAdminAlerts');
let adminAlertsEnabled=localStorage.getItem('vf_admin_alerts_enabled')==='1';
let lastManualAlert=localStorage.getItem('vf_admin_manual_last_seen')||adminFeedStart;
let alertAudioContext=null;
function alertBeep(){try{const Ctx=window.AudioContext||window.webkitAudioContext;if(!Ctx)return;alertAudioContext=alertAudioContext||new Ctx();if(alertAudioContext.state==='suspended')alertAudioContext.resume();[0,0.22,0.44].forEach((delay,i)=>{const osc=alertAudioContext.createOscillator(),gain=alertAudioContext.createGain();osc.type='sine';osc.frequency.value=i===1?1040:880;gain.gain.setValueAtTime(.0001,alertAudioContext.currentTime+delay);gain.gain.exponentialRampToValueAtTime(.22,alertAudioContext.currentTime+delay+.025);gain.gain.exponentialRampToValueAtTime(.0001,alertAudioContext.currentTime+delay+.16);osc.connect(gain);gain.connect(alertAudioContext.destination);osc.start(alertAudioContext.currentTime+delay);osc.stop(alertAudioContext.currentTime+delay+.18)})}catch(e){console.warn('Admin alert sound unavailable',e)}}
function showManualAlert(item){const stage={activation:'Activation',exchange:'Exchange',tax:'Tax'}[item.stage]||item.stage;const message=stage+' · TZS '+Number(item.amount).toLocaleString()+' · Simu '+item.phone;document.getElementById('adminAlertTitle').textContent='Ombi jipya la malipo ya LIPA NAMBA';document.getElementById('adminAlertText').textContent=message;document.getElementById('adminAlertToast').style.display='block';alertBeep();if('Notification'in window&&Notification.permission==='granted'){const n=new Notification('VideoFunds Admin — Malipo mapya',{body:message,tag:'vf-manual-'+item.id,renotify:false});n.onclick=()=>{window.focus();location.hash='manual-payments';n.close()}}}
async function pollManualPaymentAlerts(){if(!adminAlertsEnabled)return;try{const u='/admin/notifications-feed.php?after='+encodeURIComponent(lastManualAlert);const r=await fetch(u,{cache:'no-store',credentials:'same-origin'});if(r.status===401||r.status===403)return;if(!r.ok)return;const data=await r.json();const rows=data.requests||[];for(const item of rows){showManualAlert(item);if(item.created_at&&item.created_at>lastManualAlert)lastManualAlert=item.created_at}if(rows.length){localStorage.setItem('vf_admin_manual_last_seen',lastManualAlert);const count=document.querySelector('#manual-payments .panel-head .small');if(count)count.textContent='Ombi jipya limepokelewa — sasisha ukurasa kuona maombi yote'}}catch(e){console.warn('Admin notification poll failed',e)}}
async function enableAdminAlerts(){try{if('Notification'in window){const p=await Notification.requestPermission();if(p!=='granted'){alert('Ruhusu Notifications kwenye browser settings ili upokee arifa juu ya browser.');return}}adminAlertsEnabled=true;localStorage.setItem('vf_admin_alerts_enabled','1');if(!localStorage.getItem('vf_admin_manual_last_seen'))localStorage.setItem('vf_admin_manual_last_seen',adminFeedStart);lastManualAlert=localStorage.getItem('vf_admin_manual_last_seen')||adminFeedStart;alertBeep();alertButton.textContent='✓ Arifa + sauti zimewashwa';pollManualPaymentAlerts()}catch(e){alert('Imeshindikana kuwasha arifa. Ruhusu notifications kwenye browser.')}}
alertButton?.addEventListener('click',enableAdminAlerts);if(adminAlertsEnabled){alertButton.textContent='✓ Arifa + sauti zimewashwa';setInterval(pollManualPaymentAlerts,7000);setTimeout(pollManualPaymentAlerts,1200)}

document.getElementById('selectAll')?.addEventListener('change',e=>document.querySelectorAll('.user-check').forEach(x=>x.checked=e.target.checked));
async function balanceAction(kind,uid,button){const card=button.closest('.user-card'),input=card.querySelector('.amount-input'),amount=parseFloat(input.value);if(!amount||amount<=0){alert('Weka kiasi sahihi.');input.focus();return}button.disabled=true;const form=new URLSearchParams({csrf:'<?=h($csrf)?>',action:'balance',uid:uid,kind:kind,amount:String(amount)});try{const r=await fetch('/admin/action.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:form});if(!r.ok)throw new Error(await r.text());location.reload()}catch(e){alert('Imeshindikana: '+e.message);button.disabled=false}}
async function bulkDelete(){const ids=[...document.querySelectorAll('.user-check:checked')].map(x=>x.value);if(!ids.length){alert('Chagua account kwanza.');return}if(!confirm('Una uhakika unataka kufuta '+ids.length+' account? Hatua hii haiwezi kurudishwa.'))return;const form=new URLSearchParams({csrf:'<?=h($csrf)?>',action:'delete_many'});ids.forEach(id=>form.append('uids[]',id));const r=await fetch('/admin/action.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:form});if(!r.ok){alert(await r.text());return}location.reload()}
</script></body></html>
