<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user();
$uid=(string)$user['id'];
$profile=sb_profile($uid);
if ((string)($profile['activation_payment_status']??'')==='paid' || !empty($profile['activated'])) {
    vf_flow_redirect_for_profile($profile);
}
vf_flow_update($uid,['withdrawal_flow_stage'=>'activation']);
$name=vf_display_name($profile,$user);
$amountText=number_format(vf_stage_amounts()['activation'],0,'.',',');
$pageTitle='VideoFunds — Activation';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <?= vf_balance_line($profile) ?>
  <h1>Preparation for withdrawal of money is in progress.</h1>
  <p class="dear">Dear <?= vf_esc($name) ?></p>
  <p class="message">Your account is waiting for Activation. Complete this step to continue with the withdrawal process.</p>
  <p class="phone-note">Activation payment</p>
  <div class="amount">TZS <?= $amountText ?></div>
  <p class="subnote">Choose your mobile money network and confirm the payment details. Your account status changes only after the payment is verified.</p>
  <a class="flow-action" href="paymentmethod.php?stage=activation">Pay Activation fee</a>
</main>
<?php require __DIR__.'/flow-footer.php'; ?>
