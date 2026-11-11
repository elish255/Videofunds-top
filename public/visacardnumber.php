<?php
require_once __DIR__.'/supabase.php';
$user=sb_require_user();
$profile=sb_profile((string)$user['id']);
$balance=(float)($profile['balance']??0);
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Card withdrawal</title><style>body{font:16px Arial;background:#f4f6fa;margin:0;display:grid;place-items:center;min-height:100vh}.box{background:white;padding:28px;border-radius:14px;max-width:460px;margin:18px;box-shadow:0 8px 30px #0001}a{display:inline-block;margin-top:16px;background:#1767c5;color:white;padding:11px 16px;border-radius:8px;text-decoration:none}</style><div class="box"><h2>Card withdrawal</h2><p>Card withdrawals are temporarily unavailable. For your security, this site does not collect or store full card numbers or CVC codes.</p><p>Current balance: $<?=number_format($balance,2)?></p><a href="choosepayment.php">Back to withdrawal methods</a></div></html>
