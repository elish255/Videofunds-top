<?php
require_once __DIR__.'/flow_common.php';
$user=sb_require_user(); $uid=(string)$user['id']; $profile=sb_profile($uid);
$id=trim((string)($_GET['id']??''));

// Step 1: first visit after the Tax payment is verified -> submit the withdrawal once (atomic), then redirect (reload-safe).
if ($id==='') {
    if (!vf_all_stages_paid($profile) || !vf_has_draft($profile)) { header('Location: '.(vf_flow_next_url($profile)??'dashboard.php')); exit; }
    try {
        $res=sb_rpc('vf_submit_withdrawal',['p_user'=>$uid]);
        $newId=(string)($res['id']??'');
        if ($newId==='') throw new RuntimeException('Withdrawal was not created.');
        header('Location: confirmation1.php?id='.rawurlencode($newId)); exit;
    } catch (Throwable $e) {
        error_log('VideoFunds confirmation1: '.$e->getMessage());
        $message=str_contains($e->getMessage(),'Insufficient balance')
            ? 'Your balance is no longer enough for this withdrawal amount. Please start a new withdrawal.'
            : 'We could not complete your withdrawal request. Please contact support.';
        $pageTitle='VideoFunds — Withdrawal';
        require __DIR__.'/flow-header.php';
        echo '<main class="flow-content"><h1 style="color:#dc3545">Withdrawal not completed</h1><p class="message">'.vf_esc($message).'</p><a class="flow-action" href="dashboard.php">Back to Dashboard</a></main>';
        require __DIR__.'/flow-footer.php'; exit;
    }
}

// Step 2: show the final message for that withdrawal.
if (!ctype_digit($id)) { header('Location: dashboard.php'); exit; }
$r=sb_request('GET','/rest/v1/vf_withdrawals?select=id,amount,method,account_number,status,created_at&id=eq.'.rawurlencode($id).'&user_id=eq.'.rawurlencode($uid).'&limit=1');
$w=$r['data'][0]??null;
if (!$w) { header('Location: dashboard.php'); exit; }
$name=vf_display_name($profile,$user);
$amountUsd=number_format((float)$w['amount'],2);
$target=vf_account_label((string)$w['method'],(string)$w['account_number']);
$pageTitle='VideoFunds — Withdrawal';
require __DIR__.'/flow-header.php';
?>
<main class="flow-content">
  <h1>🎉 Congratulations!</h1>
  <p class="dear">Dear <?= vf_esc($name) ?>,</p>
  <p class="message">You have successfully made a withdrawal request of $<?= $amountUsd ?>.<br>Your amount will arrive at your <?= vf_esc($target) ?> within 24 hours.<br><strong>PLEASE WAIT!!</strong></p>
  <p class="subnote">Your request is now <b>Pending</b> in your Withdrawal History. It changes to Approved and then Paid once the admin processes it.</p>
  <?= vf_balance_line($profile) ?>
  <a class="flow-action" style="background:#149210;color:#fff!important;padding:11px 26px;font-size:16px" href="dashboard.php">Go to Dashboard</a>
  <div><a class="flow-link" href="choosepayment.php#history">View Withdrawal History</a></div>
</main>
<?php require __DIR__.'/flow-footer.php'; ?>
