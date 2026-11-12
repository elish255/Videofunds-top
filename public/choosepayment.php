<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user();
$uid=(string)$user['id'];
$profile=sb_profile($uid);
$balance=(float)($profile['balance']??0);
$minWithdrawal=200.00;
$allPaid=vf_all_stages_paid($profile);
if($allPaid && vf_has_draft($profile)){ header('Location: confirmation1.php'); exit; }
if($allPaid){
  // Completed cycle without a pending request: start a fresh Activation cycle.
  vf_flow_update($uid,['activated'=>false,'activation_payment_status'=>'unpaid','activation_paid_at'=>null,'activation_payment_reference'=>null,'exchange_payment_status'=>'unpaid','exchange_paid_at'=>null,'exchange_payment_reference'=>null,'tax_payment_status'=>'unpaid','tax_paid_at'=>null,'tax_payment_reference'=>null,'membership'=>'ACTIVATION','withdrawal_flow_stage'=>'choose_withdrawal']);
  $profile=array_merge($profile,['activated'=>false,'activation_payment_status'=>'unpaid','exchange_payment_status'=>'unpaid','tax_payment_status'=>'unpaid','membership'=>'ACTIVATION','withdrawal_flow_stage'=>'choose_withdrawal']);
}
$continueUrl=vf_flow_next_url($profile);
$inFlow=$continueUrl!==null;
$canWithdraw=$balance>=$minWithdrawal;
if(!$inFlow && $canWithdraw) vf_flow_update($uid,['withdrawal_flow_stage'=>'choose_withdrawal']);
$withdrawals=[];
try{
  $wr=sb_request('GET','/rest/v1/vf_withdrawals?select=id,amount,method,account_number,status,created_at,updated_at&user_id=eq.'.rawurlencode($uid).'&order=created_at.desc&limit=100');
  $withdrawals=is_array($wr['data']??null)?$wr['data']:[];
}catch(Throwable $e){error_log('Withdrawal history: '.$e->getMessage());}
$rate=(float)($profile['exchange_rate']??1);if($rate<=0)$rate=1.0;
$currency=(string)($profile['currency_code']??'USD');
$symbols=['USD'=>'$','TZS'=>'TSh','KES'=>'KSh','EUR'=>'€','GBP'=>'£','NGN'=>'₦','INR'=>'₹'];
$localSymbol=$symbols[$currency]??$currency;
$statusLabels=['pending'=>'Pending','approved'=>'Approved','paid'=>'Paid','rejected'=>'Rejected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Choose Withdrawal Method - VideoFunds</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font.css">
    <link rel="stylesheet" href="css/main.css?rstr=532909801696">
    <link href="css/business-frontpage.css?rstr=532909801696" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <style>
        :root { --primary-color:#68162e; --secondary-color:#c92121; --accent-color:blue; --success-color:#149210; }
        body { background-image:url(images/background.jpg); background-size:cover; background-attachment:fixed; }
        .page-header { text-align:center; margin:2rem 0; color:#18610A; font-size:2rem; text-shadow:2px 2px 4px rgba(0,0,0,0.5); }
        .form-container { 
            max-width:900px; 
            margin:2rem auto; 
            padding:2rem; 
            background: transparent; 
            border-radius:15px;
            backdrop-filter: blur(3px);
        }
        .payment-card { 
            transition:transform 0.2s; 
            cursor:pointer; 
            border:1px solid #ddd; 
            border-radius:10px; 
            padding:20px; 
            margin:15px; 
            text-align:center; 
            background:#fff; 
            display:inline-block; 
            width:250px; 
        }
        .payment-card:hover { transform:scale(1.02); box-shadow:0 10px 20px rgba(0,0,0,0.1); }
        .payment-card-disabled { cursor:not-allowed; opacity:.72; }
        .payment-card-disabled:hover { transform:none; box-shadow:none; }
        .payment-img { max-width:100px; margin-bottom:15px; }
        .method-title { font-size:1.3rem; font-weight:bold; color:#18610A; }
        .balance-info { 
            text-align:center; 
            margin-bottom:1.5rem; 
            background: rgba(0,0,0,0.6); 
            padding:10px; 
            border-radius:20px; 
            width:fit-content; 
            margin-left:auto; 
            margin-right:auto;
            color: white;
            font-weight: bold;
        }
        .cancel-btn {
            background: rgba(128,128,128,0.8);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 20px;
            color: white;
            display: inline-block;
            transition: 0.2s;
        }
        .cancel-btn:hover {
            background: rgba(100,100,100,0.9);
            color: white;
            text-decoration: none;
        }
        .history-wrap { max-width:900px; margin:0 auto 2rem; padding:0 1rem; }
        .history-card { background:rgba(255,255,255,.96); border-radius:12px; padding:16px; box-shadow:0 4px 14px rgba(0,0,0,.12); }
        .history-card h2 { font-size:1.4rem; font-weight:bold; color:#18610A; margin:0 0 12px; }
        .history-table { width:100%; border-collapse:collapse; font-size:14px; }
        .history-table th { text-align:left; color:#533651; border-bottom:2px solid #e3e8ef; padding:8px 6px; }
        .history-table td { padding:9px 6px; border-bottom:1px solid #eef1f5; vertical-align:middle; }
        .st-badge { display:inline-block; padding:3px 10px; border-radius:14px; font-size:12px; font-weight:700; }
        .st-pending { background:#fff3cd; color:#8a6100; } .st-approved { background:#d6e8ff; color:#0b4f9e; }
        .st-paid { background:#d4f4dd; color:#136c2e; } .st-rejected { background:#fde2e4; color:#a4161a; }
        .history-empty { color:#68778a; text-align:center; padding:16px 0; }
        .flow-banner { max-width:620px; margin:0 auto 18px; background:#fff8e1; border:1px solid #f5d27a; color:#6b4e00; padding:14px 16px; border-radius:12px; text-align:center; font-weight:600; }
        .flow-banner a { display:inline-block; margin-top:8px; background:#149210; color:#fff; padding:8px 20px; border-radius:20px; text-decoration:none; }
        .method-note { text-align:center; color:#fff; background:rgba(0,0,0,.6); border-radius:14px; padding:12px 16px; max-width:620px; margin:0 auto 14px; }
        footer { background:rgba(0,0,0,0.8); color:white; text-align:center; padding:1.5rem; margin-top:2rem; }
        body { padding-top: 80px; }
        @media (max-width:768px) { 
            .payment-card { width:90%; margin:10px auto; display:block; } 
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./">
            <font class="font9" style="font-weight:bold;font-size:30px;color:blue;">Videofunds</font>
        </a>
        <div class="d-lg-none d-md-block d-sm-block text-right">
            <a class="nav-link" href="dashboard.php"><div class="fa-address-card"> User area</div></a>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
            aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="dark-blue-text"><i class="fas fa-bars fa-1x"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="./"><div class="fa-home"> Main</div></a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><div class="fa-address-card"> User area</div></a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><div class="fa-sign-in"> Sign Out</div></a></li>
                <li class="nav-item"><a class="nav-link" href="https://wa.me/+639126610699"><div class="fa-question-circle"> Support</div></a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center mt-3">
            <font class="font9" style="font-size:2rem;font-weight:bold;color:#18610A;">Choose Withdrawal Method</font>
        </div>
    </div>
</div>

<div class="form-container">
    <div class="balance-info">
        💰 Your Balance: $<?php echo number_format($balance, 2); ?> | 
        Min: $<?php echo number_format($minWithdrawal, 2); ?>
    </div>
<?php if ($inFlow): ?>
    <div class="flow-banner">
        You have a withdrawal in progress<?php if (vf_has_draft($profile)): ?> ($<?php echo number_format((float)$profile['wd_amount'], 2); ?>)<?php endif; ?>.<br>
        <a href="<?php echo vf_esc($continueUrl); ?>">Continue withdrawal</a>
    </div>
<?php elseif (!$canWithdraw): ?>
    <div class="method-note">You need at least $<?php echo number_format($minWithdrawal, 2); ?> to start a withdrawal. Keep watching videos to grow your balance.</div>
<?php else: ?>
    <div style="text-align:center;">
        <div class="payment-card payment-card-disabled" aria-disabled="true" title="Card payouts are unavailable until a tokenized payout provider is connected">
            <img src="images/paysystems/en/card.png" class="payment-img" alt=""><br>
            <div class="method-title">Card payouts unavailable</div>
            <p>We do not collect full card numbers or security codes.</p>
        </div>
        <div class="payment-card" onclick="location.href='enterphonenumber.php';">
            <img src="images/paysystems/en/phone.png" class="payment-img"><br>
            <div class="method-title">Phone Bill</div>
            <p>Withdrawal using phone bill</p>
        </div>
        <div class="payment-card" onclick="location.href='paypal.php';">
            <img src="images/paysystems/en/paypal.png" class="payment-img"><br>
            <div class="method-title">PayPal</div>
            <p>Withdrawal using PayPal</p>
        </div>
    </div>
<?php endif; ?>
    <div style="text-align:center; margin-top:20px;">
        <a href="dashboard.php" class="cancel-btn">Back to Dashboard</a>
    </div>
</div>

<section class="history-wrap" id="history">
    <div class="history-card">
        <h2>Withdrawal History</h2>
<?php if (!$withdrawals): ?>
        <div class="history-empty">No withdrawals yet. Your transactions will appear here.</div>
<?php else: ?>
        <div style="overflow-x:auto">
        <table class="history-table">
            <thead><tr><th>Date</th><th>Amount</th><th>To</th><th>Status</th></tr></thead>
            <tbody>
<?php foreach ($withdrawals as $w):
    $st = strtolower((string)($w['status'] ?? 'pending'));
    if (!isset($statusLabels[$st])) $st = 'pending';
    $ts = strtotime((string)($w['created_at'] ?? '')) ?: time();
    $amt = (float)($w['amount'] ?? 0);
?>
                <tr>
                    <td><?php echo vf_esc(date('d M Y, H:i', $ts)); ?></td>
                    <td><b>$ <?php echo number_format($amt, 2); ?></b><br><small style="color:#68778a"><?php echo vf_esc($localSymbol); ?> <?php echo number_format($amt * $rate, 2); ?></small></td>
                    <td><?php echo vf_esc(vf_account_label((string)($w['method'] ?? 'phone'), (string)($w['account_number'] ?? ''))); ?></td>
                    <td><span class="st-badge st-<?php echo $st; ?>"><?php echo $statusLabels[$st]; ?></span></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
        </div>
<?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
