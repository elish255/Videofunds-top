<?php
require_once __DIR__.'/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['fullname'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');
$ref = trim($_POST['referral_code'] ?? '');
$geo = vf_detect_country_currency();
$country = $geo['country_code'];
$currency = $geo['currency_code'];
$rate = vf_usd_exchange_rate($currency);

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($password) < 8 || $password !== $confirm) {
    header('Location: index.php?error=invalid_registration');
    exit;
}

try {
    // First try to create the Auth account.
    try {
        $auth = sb_auth('signup', [
            'email' => $email,
            'password' => $password,
            'data' => ['full_name' => $name]
        ]);
    } catch (Throwable $signupError) {
        // A previous attempt may have created auth.users but failed before
        // inserting the matching VideoFunds profile. Recover that account.
        $message = strtolower($signupError->getMessage());
        $isDuplicate = str_contains($message, 'already registered') ||
                       str_contains($message, 'user already exists') ||
                       str_contains($message, 'already been registered');

        if (!$isDuplicate) {
            throw $signupError;
        }

        $auth = sb_auth('token?grant_type=password', [
            'email' => $email,
            'password' => $password
        ]);
    }

    // Supabase may return a user without a session if email confirmation
    // is still enabled. Try immediate password login; Confirm email must be OFF.
    if (empty($auth['access_token'])) {
        $auth = sb_auth('token?grant_type=password', [
            'email' => $email,
            'password' => $password
        ]);
    }

    if (empty($auth['access_token']) || empty($auth['user']['id'])) {
        throw new RuntimeException(
            'No login session returned. In Supabase Auth > Providers > Email, turn OFF Confirm email.'
        );
    }

    // Ensure profile exists even if an earlier signup partially completed.
    if (!function_exists('sb_ensure_profile')) {
        throw new RuntimeException('Profile recovery helper is missing from supabase.php.');
    }

    sb_ensure_profile($auth['user'], [
        'email' => $email,
        'full_name' => $name,
        'referred_by' => $ref !== '' ? $ref : null,
        'country_code' => $country,
        'currency_code' => $currency,
        'exchange_rate' => $rate
    ]);

    sb_set_auth_cookies($auth);
    header('Location: dashboard.php');
    exit;

} catch (Throwable $e) {
    error_log('VideoFunds registration/recovery: '.$e->getMessage());

    $message = strtolower($e->getMessage());
    if (str_contains($message, 'invalid login credentials')) {
        header('Location: index.php?error=account_exists');
    } else {
        header('Location: index.php?error=registration_failed');
    }
    exit;
}
?>