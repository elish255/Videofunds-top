<?php
require_once __DIR__.'/flow_common.php'; sb_require_user();
$stage=$_GET['stage']??'activation';$ref=$_GET['ref']??'';
if(!in_array($stage,['activation','exchange','tax'],true)||$ref===''){header('Location: dashboard.php');exit;}
$next=['activation'=>'exchange.php','exchange'=>'tax.php','tax'=>'withdrawal-processing.php'][$stage];
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment Status</title><link rel="stylesheet" href="style.css"><style>body{background:#06101d;color:#fff;font-family:Arial}.box{max-width:520px;margin:70px auto;padding:30px;background:#0d1b2a;border-radius:18px;text-align:center}.spin{font-size:45px}.muted{color:#aebdce}</style></head><body><main class="box"><div class="spin">⏳</div><h1>Inasubiri uthibitisho wa malipo…</h1><p class="muted">Njia ya malipo: Mobile Money</p><p id="status" class="muted">pending</p></main><script>
const stage=<?=json_encode($stage)?>,ref=<?=json_encode($ref)?>,next=<?=json_encode($next)?>;
async function poll(){try{const r=await fetch('payment-gateway.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'status',reference:ref}),cache:'no-store'});const d=await r.json();const status=d.payment_status||'pending';document.getElementById('status').textContent=status;if(status==='success'){location.href=next;return;}if(status==='cancelled'||status==='failed'){document.getElementById('status').textContent='Payment cancelled';return;}}catch(e){}setTimeout(poll,1800)}poll();
</script></body></html>
