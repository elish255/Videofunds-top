<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
if (!vf_stage_paid($profile,'activation')) { header('Location: endpay.php'); exit; }
if (!vf_has_draft($profile)) { header('Location: index-exchange.php'); exit; }
if (vf_stage_paid($profile,'exchange')) { header('Location: newindex.php'); exit; }
vf_flow_update($uid,['withdrawal_flow_stage'=>'exchange']);
$name=vf_display_name($profile,$user);
$amountUsd=number_format((float)$profile['wd_amount'],2);
$target=vf_account_label((string)($profile['wd_method']??'phone'),(string)($profile['wd_account']??''));
$feeText=number_format(vf_stage_amounts()['exchange'],0,'.',',');
$pageTitle='VideoFunds — Exchange';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <?= vf_balance_line($profile) ?>
  <h1>Preparation for withdrawal of money is in progress.</h1>
  <p class="dear">Dear <?= vf_esc($name) ?>,</p>
  <p class="message">Your Amount ($<?= $amountUsd ?>) will be Sent to your provided <?= vf_esc($target) ?> after Exchanged From USD Dollar to TSH.<br>You are Required to Pay Exchange Fee of <?= $feeText ?>/-Tsh.</p>
  <a class="flow-action" style="background:#149210;color:#fff!important;padding:11px 26px;font-size:16px" href="paymentmethod.php?stage=exchange">Make Payment</a>
  <div><a class="flow-link" href="index-exchange.php">Change withdrawal details</a></div>
</main>
<?php require __DIR__.'/flow-footer.php'; ?>
