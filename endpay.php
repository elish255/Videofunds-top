<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
vf_demo_require_balance($profile);
if ((string)($profile['activation_payment_status']??'')==='paid' || !empty($profile['activated'])) { vf_flow_redirect_for_profile($profile); }
vf_flow_update($uid,['withdrawal_flow_stage'=>'activation']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>VideoFunds — Activation / Withdrawal Requirements</title><link rel="stylesheet" href="style.css"><style>
body{background:#06101d;color:#fff;font-family:Arial,sans-serif}.flow{max-width:560px;margin:50px auto;padding:28px;background:#0d1b2a;border:1px solid #21344b;border-radius:18px}.amount{font-size:34px;font-weight:800;color:#55e0c2}.btn{display:block;text-align:center;padding:14px;border-radius:12px;background:#55e0c2;color:#06101d;text-decoration:none;font-weight:800;margin-top:22px}.note{font-size:13px;color:#aebdce;line-height:1.6;margin-top:15px}.demo{display:inline-block;padding:5px 9px;border-radius:20px;background:#24384e;color:#9debd8;font-size:11px;font-weight:700}
</style></head><body><main class="flow"><h1>Activation / Withdrawal Requirements</h1><p>Account balance: <b>$<?=number_format((float)$profile['balance'],2)?></b></p><p>Kiasi cha malipo ya activation</p><div class="amount">TZS 35,500</div><p class="note">Malipo yatafanyika kupitia Mobile Money. Akaunti itaendelea baada ya uthibitisho wa muamala.</p><a class="btn" href="paymentmethod.php?stage=activation">LIPA Sasa</a></main></body></html>
