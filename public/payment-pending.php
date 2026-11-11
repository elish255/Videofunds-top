<?php
require_once __DIR__.'/flow_common.php';
sb_require_user();
$stage=$_GET['stage']??'activation';$ref=trim((string)($_GET['ref']??''));
if(!in_array($stage,['activation','exchange','tax'],true)||$ref===''){header('Location: paymentmethod.php');exit;}
$next=['activation'=>'index-exchange.php','exchange'=>'newindex.php','tax'=>'confirmation1.php'][$stage];
?><!doctype html><html lang="sw"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#68162e"><title>Hali ya Malipo — VideoFunds</title><link rel="stylesheet" href="css/bootstrap.css"><link rel="stylesheet" href="css/main.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"><style>
body{font-family:Inter,Arial,sans-serif;background:#68162e url('background.png') center/cover fixed no-repeat;min-height:100vh;padding:85px 15px 30px;color:#27364a}body:before{content:"";position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:-1}.navbar{background:#68162e!important}.navbar-brand{color:white!important;font-weight:800}.box{max-width:520px;margin:35px auto;padding:30px 24px;background:rgba(255,255,255,.98);border-radius:22px;text-align:center;box-shadow:0 16px 36px rgba(0,0,0,.28)}.spin{font-size:44px}.muted{color:#718096}.status{font-size:14px;font-weight:700;color:#68162e;margin:18px 0}.back{display:inline-block;margin-top:10px;color:#68162e;font-weight:700}
</style></head><body><nav class="navbar navbar-expand-lg fixed-top"><div class="container"><a class="navbar-brand" href="index.php">VideoFunds</a><a class="nav-link" href="logout.php">Logout</a></div></nav><main class="box"><div class="spin" id="icon">⏳</div><h1 style="font-size:22px;font-weight:800;color:#68162e">Inasubiri uthibitisho wa malipo</h1><p class="muted">Thibitisha ombi lililotumwa kwenye simu yako.</p><p id="status" class="status">Tunasubiri uthibitisho wa muamala...</p><p id="detail" class="muted">Usifunge ukurasa huu mpaka malipo yahakikiwe.</p><a class="back" href="paymentmethod.php?stage=<?=htmlspecialchars($stage,ENT_QUOTES)?>">Rudi kwenye malipo</a></main>
<script>
const ref=<?=json_encode($ref)?>,next=<?=json_encode($next)?>;let stopped=false;
async function poll(){if(stopped)return;try{const r=await fetch('payment-gateway.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({action:'status',reference:ref}),cache:'no-store'});const d=await r.json();const status=String(d.payment_status||'pending').toLowerCase();document.getElementById('status').textContent='Hali: '+status;
 if(status==='success'){stopped=true;document.getElementById('icon').textContent='✅';document.getElementById('status').textContent='Malipo yamepokelewa!';document.getElementById('detail').textContent='Malipo yamekamilika. Tunakurudisha dashboard...';setTimeout(()=>location.href=next,900);return;}
 if(['failed','cancelled','expired'].includes(status)){stopped=true;document.getElementById('icon').textContent='❌';document.getElementById('detail').textContent='Malipo hayajakamilika. Rudi kwenye ukurasa wa malipo ujaribu tena.';return;}
 }catch(e){document.getElementById('detail').textContent='Tunajaribu kuangalia hali ya muamala tena...';}
 setTimeout(poll,5000);
}poll();
</script></body></html>