<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
if (!vf_stage_paid($profile,'activation')) { header('Location: endpay.php'); exit; }
if (vf_stage_paid($profile,'exchange') && vf_has_draft($profile)) { header('Location: newindex.php'); exit; }
$exchangePaid=vf_stage_paid($profile,'exchange');

$balance=(float)($profile['balance']??0);
$method=in_array((string)($profile['wd_method']??''),['phone','visa','paypal'],true)?(string)$profile['wd_method']:'phone';
$minWithdrawal=200.00;
$error='';
$amountValue=vf_has_draft($profile)?number_format((float)$profile['wd_amount'],2,'.',''):'';
$accountValue=(string)($profile['wd_account']??'');

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $amountValue=trim((string)($_POST['amount']??''));
    $accountValue=trim((string)($_POST['account']??''));
    $amount=round((float)str_replace(',','',$amountValue),2);
    $account=$accountValue;
    if ($method==='phone') {
        $digits=preg_replace('/\D+/','',$account);
        if (str_starts_with($digits,'0')) $digits='255'.substr($digits,1);
        $account='+'.$digits;
        if (!preg_match('/^\+255\d{9}$/',$account)) $error='Invalid phone number. Format: +255XXXXXXXXX';
    } elseif ($method==='visa') {
        $account=preg_replace('/\D+/','',$account);
        if (strlen($account)<13||strlen($account)>19) $error='Enter a valid card number.';
    } else {
        if (!filter_var($account,FILTER_VALIDATE_EMAIL)) $error='Enter a valid PayPal email.';
    }
    if ($error==='') {
        if ($amount<=0) $error='Enter a valid amount.';
        elseif ($amount<$minWithdrawal) $error='Minimum withdrawal amount is $'.number_format($minWithdrawal,2).'.';
        elseif ($amount>$balance) $error='Amount exceeds your current balance ($'.number_format($balance,2).').';
    }
    if ($error==='') {
        try {
            vf_flow_update($uid,['wd_amount'=>$amount,'wd_method'=>$method,'wd_account'=>$account,'withdrawal_flow_stage'=>$exchangePaid?'tax':'exchange']);
            header('Location: '.($exchangePaid?'newindex.php':'exchange.php')); exit;
        } catch (Throwable $e) {
            error_log('VideoFunds index-exchange: '.$e->getMessage());
            $error='Could not save your request. Please try again.';
        }
    }
} elseif (!$exchangePaid) {
    vf_flow_update($uid,['withdrawal_flow_stage'=>'exchange']);
}

$name=vf_display_name($profile,$user);
$labels=['phone'=>['Enter Phone Number:','+255XXXXXXXXX','tel'],'visa'=>['Enter Card Number:','Card number','tel'],'paypal'=>['Enter PayPal Email:','name@example.com','email']];
[$accountLabel,$accountPlaceholder,$accountType]=$labels[$method];
$pageTitle='VideoFunds — Withdrawal';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <?= vf_balance_line($profile) ?>
  <h1>Congratulations! Your Account is Now Approved for Withdrawal</h1>
  <p class="dear">Dear <?= vf_esc($name) ?></p>
  <p class="message" style="margin-bottom:0">Please Enter Amount you want to Withdrawal</p>
  <?php if ($error!==''): ?><div class="flow-error" role="alert"><?= vf_esc($error) ?></div><?php endif; ?>
  <form class="flow-card" method="post" id="withdrawForm" novalidate>
    <label for="amount">Amount in USD (Min $<?= number_format($minWithdrawal,0) ?>):</label>
    <input type="text" id="amount" name="amount" inputmode="decimal" placeholder="Enter amount" required maxlength="11" autocomplete="off" value="<?= vf_esc($amountValue) ?>">
    <div class="hint">Available: $ <?= number_format($balance,2) ?></div>
    <label for="account"><?= vf_esc($accountLabel) ?></label>
    <input type="<?= vf_esc($accountType) ?>" id="account" name="account" placeholder="<?= vf_esc($accountPlaceholder) ?>" required autocomplete="off" value="<?= vf_esc($accountValue) ?>">
    <button type="submit" class="flow-btn">Confirm</button>
  </form>
</main>
<script>
document.getElementById('amount').addEventListener('input',function(){
  var v=this.value.replace(/[^0-9.]/g,''), i=v.indexOf('.');
  if(i>=0) v=v.slice(0,i+1)+v.slice(i+1).replace(/\./g,'').slice(0,2);
  this.value=v;
});
</script>
<?php require __DIR__.'/flow-footer.php'; ?>
