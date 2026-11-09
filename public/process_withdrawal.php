<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'firebase_config.php';
require_once __DIR__ . '/account_guard.php';

$uid = $_SESSION['user_id'];
$token = $_SESSION['id_token'];
$method = $_POST['method'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);

// Validate amount
if ($amount <= 0) {
    header('Location: choosepayment.php?error=invalid_amount');
    exit;
}

// Minimum withdrawal amount is now $200
if ($amount < 200) {
    header('Location: choosepayment.php?error=min_amount');
    exit;
}

// Validate method-specific details
switch ($method) {
    case 'visa':
        $card = trim($_POST['card_number'] ?? '');
        if (strlen($card) < 13) {
            header('Location: choosepayment.php?error=invalid_card');
            exit;
        }
        break;
    case 'paypal':
        $email = trim($_POST['paypal_email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: choosepayment.php?error=invalid_email');
            exit;
        }
        break;
    case 'phone':
        $phone = trim($_POST['nschphone'] ?? '');
        if (empty($phone)) {
            header('Location: choosepayment.php?error=invalid_phone');
            exit;
        }
        break;
    default:
        header('Location: choosepayment.php?error=invalid_method');
        exit;
}

// (Optional) Check if user has enough balance – but do NOT deduct
$userUrl = "https://firestore.googleapis.com/v1/projects/" . FIRESTORE_PROJECT_ID . "/databases/(default)/documents/users/$uid";
$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$resp = curl_exec($ch);
curl_close($ch);
$userData = json_decode($resp, true);
$currentBalance = $userData['fields']['balance']['doubleValue'] ?? 0;

if ($amount > $currentBalance) {
    header('Location: choosepayment.php?error=insufficient_funds');
    exit;
}

// ========== NO DEDUCTION, NO WITHDRAWAL REQUEST ==========
// Simply redirect to endpay.php for all methods
header('Location: endpay.php');
exit;
?>