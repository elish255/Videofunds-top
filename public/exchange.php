<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
if ((string)($profile['activation_payment_status']??'')!=='paid' && empty($profile['activated'])) { header('Location: endpay.php'); exit; }
if ((string)($profile['exchange_payment_status']??'')==='paid') { header('Location: tax.php'); exit; }
vf_flow_update($uid,['withdrawal_flow_stage'=>'exchange']);
$pageTitle='VideoFunds — Exchange';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <h1>Preparation for withdrawal of money is in progress.</h1>
  <p class="dear">Dear user</p>
  <p class="message">Your Activation stage is complete. Complete the Exchange step to proceed to the next stage.</p>
  <p class="phone-note">Exchange payment</p>
  <div class="amount">TZS 40,820</div>
  <p class="subnote">Choose your mobile money network and confirm the payment details. The next stage opens after the payment is verified.</p>
  <a class="flow-action" href="paymentmethod.php?stage=exchange">Pay Exchange fee</a>
</main>
<?php require __DIR__.'/flow-footer.php'; ?>
