<?php
// payment-proxy.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Your API keys
define('ACCESS_KEY', getenv('PAYMENT_ACCESS_KEY') ?: '');
define('SECRET_KEY', getenv('PAYMENT_SECRET_KEY') ?: '');

// Get the request
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Handle different actions
if ($action === 'initiate_payment') {
    $phoneNumber = $input['phone_number'] ?? '';
    $amount = $input['amount'] ?? 62500;
    
    // Generate a unique transaction ID
    $transactionId = 'VID_' . time() . '_' . rand(10000, 99999);
    
    // Store the transaction
    $pendingFile = 'pending_transactions.json';
    $pending = [];
    if (file_exists($pendingFile)) {
        $content = file_get_contents($pendingFile);
        $pending = json_decode($content, true) ?: [];
    }
    
    $pending[$transactionId] = [
        'phone' => $phoneNumber,
        'amount' => $amount,
        'time' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];
    file_put_contents($pendingFile, json_encode($pending));
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Payment initiated successfully',
        'transaction_id' => $transactionId,
        'amount' => $amount
    ]);
    
} elseif ($action === 'check_status') {
    $transactionId = $input['transaction_id'] ?? '';
    
    // Check if transaction is completed
    $validFile = 'valid-transactions.json';
    $validTransactions = [];
    if (file_exists($validFile)) {
        $content = file_get_contents($validFile);
        $validTransactions = json_decode($content, true) ?: [];
    }
    
    $isCompleted = in_array($transactionId, $validTransactions);
    
    echo json_encode([
        'success' => true,
        'status' => $isCompleted ? 'completed' : 'pending',
        'transaction_id' => $transactionId
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
