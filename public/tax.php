<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
if ((string)($profile['activation_payment_status']??'')!=='paid' && empty($profile['activated'])) { header('Location: endpay.php'); exit; }
if ((string)($profile['exchange_payment_status']??'')!=='paid') { header('Location: exchange.php'); exit; }
if ((string)($profile['tax_payment_status']??'')==='paid') { header('Location: withdrawal-processing.php'); exit; }
vf_flow_update($uid,['withdrawal_flow_stage'=>'tax']);
$pageTitle='VideoFunds — Tax';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <h1>Preparation for withdrawal of money is in progress.</h1>
  <p class="dear">Dear user</p>
  <p class="message">Your Exchange stage is complete. Review the Tax stage payment details below before proceeding.</p>
  <p class="phone-note">Tax payment</p>
  <div class="amount">TZS 62,500</div>
  <p class="subnote">Choose your mobile money network and confirm the payment details. Processing continues only after the payment is verified. This page does not represent a tax authority notice.</p>
  <a class="flow-action" href="paymentmethod.php?stage=tax">Pay Tax fee</a>
</main>
<?php require __DIR__.'/flow-footer.php'; ?>
