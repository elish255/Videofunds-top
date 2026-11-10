<?php
// verify-transaction.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$transactionId = $_GET['transaction_id'] ?? '';

if (empty($transactionId)) {
    echo json_encode(['valid' => false, 'message' => 'No transaction ID']);
    exit();
}

// Check valid transactions
$validFile = 'valid-transactions.json';
$validTransactions = [];

if (file_exists($validFile)) {
    $content = file_get_contents($validFile);
    $validTransactions = json_decode($content, true) ?: [];
}

// Also check text backup
if (file_exists('valid_transactions.txt')) {
    $backup = file_get_contents('valid_transactions.txt');
    $backupIds = explode("\n", trim($backup));
    $validTransactions = array_merge($validTransactions, $backupIds);
    $validTransactions = array_unique($validTransactions);
}

$isValid = in_array($transactionId, $validTransactions);

echo json_encode([
    'valid' => $isValid,
    'transaction_id' => $transactionId
]);
?>