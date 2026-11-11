<?php
require_once __DIR__.'/supabase.php';

function vf_flow_profile(string $uid): array {
    return sb_profile($uid);
}

function vf_flow_stage(array $profile): string {
    $stage = (string)($profile['withdrawal_flow_stage'] ?? 'choose_withdrawal');
    $allowed = ['choose_withdrawal','activation','exchange','tax','withdrawal_processing','completed'];
    return in_array($stage, $allowed, true) ? $stage : 'choose_withdrawal';
}

function vf_flow_update(string $uid, array $data): void {
    $data['updated_at'] = date('c');
    sb_request('PATCH','/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid),$data,true,null,['Prefer: return=minimal']);
}

/** Server-side fee amounts (TZS) for each payment stage. */
function vf_stage_amounts(): array {
    return ['activation'=>35500,'exchange'=>40820,'tax'=>62500];
}

function vf_esc($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** Name shown after "Dear": the account's username (full name), falling back to the e-mail prefix. */
function vf_display_name(array $profile, array $user = []): string {
    $name = trim((string)($profile['full_name'] ?? ''));
    if ($name !== '') return $name;
    $email = (string)($user['email'] ?? $profile['email'] ?? '');
    if ($email !== '') return explode('@', $email)[0];
    return 'User';
}

function vf_stage_paid(array $profile, string $stage): bool {
    if ($stage === 'activation') {
        return (string)($profile['activation_payment_status'] ?? '') === 'paid' || !empty($profile['activated']);
    }
    return (string)($profile[$stage.'_payment_status'] ?? '') === 'paid';
}

function vf_has_draft(array $profile): bool {
    return (float)($profile['wd_amount'] ?? 0) > 0;
}

function vf_all_stages_paid(array $profile): bool {
    return vf_stage_paid($profile, 'activation') && vf_stage_paid($profile, 'exchange') && vf_stage_paid($profile, 'tax');
}

/** Same balance line as the dashboard: "$ 12.00 (TSh 31,200.00)". */
function vf_balance_line(array $profile): string {
    $symbols = ['USD'=>'$','TZS'=>'TSh','KES'=>'KSh','EUR'=>'€','GBP'=>'£','NGN'=>'₦','INR'=>'₹'];
    $balance = (float)($profile['balance'] ?? 0);
    $currency = (string)($profile['currency_code'] ?? 'USD');
    $rate = (float)($profile['exchange_rate'] ?? 1);
    if ($rate <= 0) $rate = 1.0;
    $local = $symbols[$currency] ?? $currency;
    return '<div class="vf-balance"><span class="vf-balance-label">Earned money</span>'
         . '<span class="vf-balance-value">$ '.number_format($balance, 2).' &nbsp;('.vf_esc($local).' '.number_format($balance * $rate, 2).')</span></div>';
}

function vf_account_label(string $method, string $account): string {
    if ($method === 'visa') return 'Visa card (**** '.substr(preg_replace('/\D+/', '', $account), -4).')';
    if ($method === 'paypal') return 'PayPal account ('.$account.')';
    return 'Phone number ('.$account.')';
}

/**
 * Where the user must go next in the withdrawal flow:
 * Activation -> index-exchange -> exchange (Confirmation) -> payment -> newindex (Tax) -> payment -> confirmation1 (final).
 */
function vf_flow_next_url(array $profile): ?string {
    if (!vf_stage_paid($profile, 'activation')) return null;
    if (vf_all_stages_paid($profile)) return vf_has_draft($profile) ? 'confirmation1.php' : null;
    if (!vf_stage_paid($profile, 'exchange')) return 'index-exchange.php';
    return vf_has_draft($profile) ? 'newindex.php' : 'index-exchange.php';
}

function vf_flow_redirect_for_profile(array $profile): void {
    $next = vf_flow_next_url($profile);
    if ($next !== null) { header('Location: '.$next); exit; }
    if (vf_all_stages_paid($profile)) { header('Location: dashboard.php'); exit; }
}

?>
