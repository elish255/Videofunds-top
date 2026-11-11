<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user();
$profile=sb_profile((string)$user['id']);
$stage=$_GET['stage']??'activation';
if(!in_array($stage,['activation','exchange','tax'],true)) $stage='activation';
$amount=['activation'=>35500,'exchange'=>40820,'tax'=>62500][$stage];
if(($profile[$stage.'_payment_status']??'')==='paid'){vf_flow_redirect_for_profile($profile);}
if(in_array($stage,['exchange','tax'],true) && !vf_has_draft($profile) && ($profile[$stage.'_payment_status']??'')!=='paid'){header('Location: '.(vf_stage_paid($profile,'activation')?'index-exchange.php':'endpay.php'));exit;}
$amountText=number_format($amount,0,'.',',');
$stageLabels=['activation'=>'Activation','exchange'=>'Exchange','tax'=>'Tax'];
$stageLabel=$stageLabels[$stage];
function vf_h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!doctype html>
<html lang="sw">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#68162e">
<title>Lipa — VideoFunds</title>
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="css/font.css">
<link rel="stylesheet" href="css/main.css">
<link href="css/business-frontpage.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box} body{font-family:Inter,Arial,sans-serif;background:#68162e url('background.png') center/cover fixed no-repeat;min-height:100vh;color:#26364a;padding-top:76px}
body:before{content:"";position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:-1}
.navbar{background:#68162e!important;box-shadow:0 2px 8px rgba(0,0,0,.2);padding:.5rem 1rem}
.navbar-brand{font-weight:800;font-size:1.5rem;color:#fff!important}.nav-link{color:#fff!important;font-weight:600}
.page-wrap{width:100%;max-width:560px;margin:28px auto 55px;padding:0 15px}
.payment-card{background:rgba(255,255,255,.98);border-radius:22px;overflow:hidden;box-shadow:0 16px 36px rgba(0,0,0,.28);margin-bottom:18px}
.card-head{padding:20px 22px;border-bottom:1px solid #edf0f4;display:flex;align-items:center;justify-content:space-between;gap:12px}
.card-head h1,.card-head h2{font-size:18px;font-weight:800;color:#68162e;margin:0}.muted{color:#718096;font-size:13px;margin:4px 0 0}
.form-body{padding:22px}.security{display:flex;gap:12px;background:#fff4f5;border:1px solid #f2cbd2;color:#68162e;border-radius:13px;padding:13px 15px;margin-bottom:18px;font-size:13px;line-height:1.5}
.amount{font-size:30px;font-weight:800;color:#68162e;margin:8px 0 18px}
label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:7px}
.phone-wrap{display:flex;border:1.5px solid #d8dee7;border-radius:12px;overflow:hidden;background:#fff}
.prefix{padding:0 14px;background:#f1f3f6;border-right:1px solid #d8dee7;display:flex;align-items:center;font-weight:700;color:#475569}
input[type=tel]{width:100%;padding:14px;border:0;outline:0;font:inherit;font-size:15px}
.pay-btn{width:100%;border:0;border-radius:12px;background:#68162e;color:#fff;padding:15px;margin-top:17px;font-weight:800;font-size:15px;cursor:pointer}
.pay-btn:hover{background:#814B5E}.pay-btn:disabled{opacity:.6;cursor:wait}
#msg{min-height:20px;color:#b42318;font-size:13px;margin:10px 0 0}
.powered{font-size:12px;color:#8792a2;text-align:center;margin-top:14px}
.manual-box{margin-top:22px;padding:17px;border:1px solid #ead4da;background:#fff8fa;border-radius:14px}.manual-box h2{font-size:16px;color:#68162e;font-weight:800;margin:0 0 7px}.manual-box p{font-size:13px;color:#667085;line-height:1.5}.manual-box input{width:100%;padding:13px;border:1px solid #d8dee7;border-radius:10px;font:inherit}.manual-btn{width:100%;border:0;border-radius:10px;background:#814B5E;color:white;padding:13px;margin-top:10px;font-weight:800;cursor:pointer}.manual-status{font-size:13px;margin-top:10px;color:#68162e;font-weight:700}
.ussd-title{background:#f7f8fa;padding:15px 20px;font-size:11px;letter-spacing:.8px;font-weight:800;color:#68162e}
.operator{border-top:1px solid #edf0f4}.operator-toggle{width:100%;border:0;background:#fff;padding:14px 18px;display:flex;align-items:center;gap:12px;text-align:left;font-weight:700;color:#344054;cursor:pointer}
.operator-toggle img{width:38px;height:38px;object-fit:contain;border:1px solid #e2e8f0;border-radius:9px;padding:3px}
.operator-toggle .hint{display:block;font-size:12px;color:#8792a2;font-weight:400;margin-top:2px}.operator-toggle .arrow{margin-left:auto}
.operator-steps{display:none;padding:0 20px 17px;background:#fafbfc}.operator.open .operator-steps{display:block}
.steps{list-style:none;margin:0;padding:5px 0}.steps li{padding:9px 0;border-bottom:1px dashed #e2e8f0;font-size:13px;color:#475569}
.steps li:last-child{border:0}.step-value{font-size:17px;color:#68162e;font-weight:800}.biz{background:#f9e9ed;color:#68162e;border-radius:8px;padding:9px 12px;font-size:12px}
#loader{display:none;position:fixed;inset:0;background:rgba(255,255,255,.92);z-index:2000;align-items:center;justify-content:center;text-align:center;color:#68162e}
.spinner{width:44px;height:44px;border:4px solid #ead6dc;border-top-color:#68162e;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 14px}@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:480px){.page-wrap{margin-top:18px}.form-body{padding:18px}.card-head{padding:17px}}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top"><div class="container">
<a class="navbar-brand" href="index.php">VideoFunds</a>
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#vfNav" aria-controls="vfNav" aria-expanded="false" aria-label="Fungua menyu"><i class="fa fa-bars" style="color:white"></i></button>
<div class="collapse navbar-collapse" id="vfNav"><ul class="navbar-nav ml-auto">
<li class="nav-item"><a class="nav-link" href="index.php"><i class="fa fa-home"></i> Main</a></li>
<li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa fa-user-circle"></i> User Area</a></li>
<li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
</ul></div></div></nav>
<main class="page-wrap">
<section class="payment-card">
<div class="form-body">
<div class="security"><strong>🔒</strong><span><b>LINDA PESA YAKO</b><br>Lipia kupitia mfumo rasmi wa VideoFunds. Thibitisha ombi la malipo kwenye simu yako.</span></div>
<div class="card-head" style="padding:0 0 16px;margin-bottom:17px"><div><h1>Malipo ya <?=vf_h($stageLabel)?></h1><p class="muted">Tanzania · Mobile Money</p></div><img src="https://flagcdn.com/w40/tz.png" width="30" alt="Tanzania"></div>
<label>Kiasi cha kulipia</label><div class="amount">TZS <?=$amountText?></div>
<form id="pay" novalidate>
<label for="phone">Namba ya simu ya Tanzania</label>
<div class="phone-wrap"><span class="prefix">🇹🇿 +255</span><input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" placeholder="0712345678" maxlength="13" required></div>
<button id="submit" class="pay-btn" type="submit">LIPA SASA</button><p id="msg" role="status" aria-live="polite"></p>
</form>

<div class="powered">Malipo salama ya simu au uthibitisho wa LIPA NAMBA</div>
</div></section>
<section class="payment-card">
<div class="ussd-title">NJIA ZA MALIPO / USSD MENU</div>
<?php
$operators=[
 ['id'=>'voda','name'=>'Vodacom M-Pesa','code'=>'*150*00#','logo'=>'https://brandlogos.net/wp-content/uploads/2025/04/vodacom-logo_brandlogos.net_4uzfe.png','steps'=>['Bonyeza <b>*150*00#</b>','Chagua <b>Lipa kwa M-PESA</b>','Chagua <b>LIPA KWA SIMU-HALOPESA</b>','Weka LIPA NAMBA: <b class="step-value">23513043</b>','Weka kiasi <b class="step-value">[amount]</b>','Weka namba ya siri']],
 ['id'=>'yas','name'=>'Mixx by Yas','code'=>'*150*01#','logo'=>'https://www.uminolan.co.tz/assets/images/supa-agent/mixx-by-yas-seeklogo2.png','steps'=>['Bonyeza <b>*150*01#</b>','Chagua <b>Lipa kwa simu</b>','Chagua <b>Kwenda mitandao mingine</b>','Chagua <b>HALO-PESA</b>','Weka HALO-PESA LIPA NAMBA: <b class="step-value">23513043</b>','Weka kiasi <b class="step-value">[amount]</b>','Weka namba ya siri']],
 ['id'=>'airtel','name'=>'Airtel Money','code'=>'*150*60#','logo'=>'https://nikulipe.com/wp-content/uploads/2022/09/Airtel_logo_PNG1.png','steps'=>['Bonyeza <b>*150*60#</b>','Chagua <b>Lipia Bili</b>','Chagua <b>LIPA KWA SIMU (MITANDAO YOTE)</b>','Chagua <b>LIPA KWA HALOPESA</b>','Weka kiasi <b class="step-value">[amount]</b>','Ingiza kumbukumbu ya malipo: <b class="step-value">23513043</b>','Ingiza namba ya siri kuruhusu muamala']],
 ['id'=>'halo','name'=>'Halopesa','code'=>'*150*88#','logo'=>'https://halopesa.co.tz/images/applications-system.png','steps'=>['Bonyeza <b>*150*88#</b>','Chagua <b>(5) Lipia Bidhaa</b>','Chagua <b>HALO-PESA</b>','Weka namba ya malipo: <b class="step-value">23513043</b>','Weka kiasi <b class="step-value">[amount]</b>','Ingiza namba ya siri','Bonyeza <b>1</b> kuruhusu muamala']]
];
foreach($operators as $op): ?>
<div class="operator" id="op-<?=vf_h($op['id'])?>">
<button type="button" class="operator-toggle" aria-expanded="false"><img src="<?=vf_h($op['logo'])?>" alt=""><span><?=vf_h($op['name'])?><span class="hint"><?=vf_h($op['code'])?></span></span><span class="arrow">⌄</span></button>
<div class="operator-steps"><ol class="steps"><?php foreach($op['steps'] as $step): ?><li><?=str_replace('[amount]','TZS '.$amountText,$step)?></li><?php endforeach; ?></ol><div class="biz">Jina la Biashara: <b>VIDEOFUNDS TOP</b></div></div>
</div>
<?php endforeach; ?>
</section>
<section class="payment-card" aria-labelledby="manualTitle">
<div class="form-body">
<div class="manual-box" id="manualBox">
 <h2 id="manualTitle">Umeshafanya malipo kwa LIPA NAMBA?</h2>
 <p>Baada ya kulipia TZS <?=$amountText?>, weka namba ya simu uliyotumia. Taarifa zitatumwa kwa ajili ya kuhakikiwa. Account itaendelea baada ya Malipo Kuthibishwa.</p>
 <label for="manualPhone">Namba ya simu uliyotumia kulipia</label>
 <input id="manualPhone" type="tel" inputmode="numeric" autocomplete="tel" placeholder="0712345678" maxlength="13">
 <button class="manual-btn" id="manualSubmit" type="button">NIMELIPIA — THIBITISHA</button>
 <div class="manual-status" id="manualStatus" role="status" aria-live="polite"></div>
</div>
</div>
</section>
</main>
<div id="loader"><div><div class="spinner"></div><b>Inatuma ombi la malipo...</b><p>Tafadhali subiri na uangalie simu yako.</p></div></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/umd/popper.min.js"></script><script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
<script>
const form=document.getElementById('pay'),btn=document.getElementById('submit'),msg=document.getElementById('msg'),loader=document.getElementById('loader');
const manualBtn=document.getElementById('manualSubmit'),manualStatus=document.getElementById('manualStatus');let manualPoll=null;
document.querySelectorAll('.operator-toggle').forEach(button=>button.addEventListener('click',()=>{const item=button.closest('.operator'),wasOpen=item.classList.contains('open');document.querySelectorAll('.operator').forEach(x=>{x.classList.remove('open');x.querySelector('.operator-toggle').setAttribute('aria-expanded','false')});if(!wasOpen){item.classList.add('open');button.setAttribute('aria-expanded','true')}}));
async function checkManual(id){try{const r=await fetch('manual-payment.php?action=status&id='+encodeURIComponent(id),{cache:'no-store'});const d=await r.json();if(d.status==='approved'){manualStatus.textContent='Malipo yamethibitishwa na Admin. Tunaendelea...';clearInterval(manualPoll);setTimeout(()=>location.href=d.next_url,900);}else if(d.status==='rejected'){clearInterval(manualPoll);manualBtn.disabled=false;manualStatus.textContent='Malipo hayajathibitishwa. Tafadhali lipia tena kisha tuma taarifa upya.';document.getElementById('manualPhone').value='';}else{manualStatus.textContent='Taarifa zimefika kwa Admin. Inasubiri kuhakikiwa...';}}catch(e){manualStatus.textContent='Tunasubiri majibu ya Admin...';}}
manualBtn.addEventListener('click',async()=>{manualStatus.textContent='';let phone=document.getElementById('manualPhone').value.replace(/\D/g,'');if(phone.startsWith('0'))phone='255'+phone.slice(1);else if(phone.startsWith('7')||phone.startsWith('6'))phone='255'+phone;if(!/^255[0-9]{9}$/.test(phone)){manualStatus.textContent='Weka namba sahihi ya simu ya Tanzania.';return;}manualBtn.disabled=true;try{const r=await fetch('manual-payment.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({stage:<?=json_encode($stage)?>,phone}),cache:'no-store'});const d=await r.json();if(!r.ok)throw new Error(d.message||'Imeshindikana kutuma taarifa.');manualStatus.textContent=d.message||'Taarifa zimetumwa kwa Admin.';await checkManual(d.request_id);manualPoll=setInterval(()=>checkManual(d.request_id),5000);}catch(e){manualBtn.disabled=false;manualStatus.textContent=e.message||'Hitilafu imetokea.';}});

form.addEventListener('submit',async e=>{
 e.preventDefault();msg.textContent='';
 let phone=document.getElementById('phone').value.replace(/\D/g,'');
 if(phone.startsWith('0'))phone='255'+phone.slice(1);else if(phone.startsWith('7')||phone.startsWith('6'))phone='255'+phone;
 if(!/^255[0-9]{9}$/.test(phone)){msg.textContent='Weka namba sahihi ya simu ya Tanzania.';return;}
 btn.disabled=true;loader.style.display='flex';
 try{
  const response=await fetch('payment-gateway.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({action:'create',stage:<?=json_encode($stage)?>,phone}),cache:'no-store'});
  const data=await response.json();if(!response.ok)throw new Error(data.message||'Imeshindikana kuanzisha malipo.');
  location.href='payment-pending.php?stage='+encodeURIComponent(<?=json_encode($stage)?>)+'&ref='+encodeURIComponent(data.reference);
 }catch(error){loader.style.display='none';btn.disabled=false;msg.textContent=error.message||'Hitilafu imetokea. Jaribu tena.';}
});
</script></body></html>
