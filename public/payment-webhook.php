<?php
// payment-webhook.php - For InfinityFree hosting
header('Content-Type: application/json');

// Log function for InfinityFree
function logWebhook($message) {
    $logFile = 'webhook_debug.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logWebhook("Webhook accessed via " . $_SERVER['REQUEST_METHOD']);

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logWebhook("Method not allowed: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit();
}

// Get input
$rawInput = file_get_contents('php://input');
logWebhook("Raw input: " . $rawInput);

if (empty($rawInput)) {
    $rawInput = json_encode($_POST);
}

$payload = json_decode($rawInput, true);
if (!$payload && !empty($_POST)) {
    $payload = $_POST;
}

logWebhook("Decoded payload: " . print_r($payload, true));

// Extract transaction data (adjust field names as needed)
$transactionId = $payload['transid'] ?? $payload['transaction_id'] ?? $payload['reference'] ?? '';
$status = $payload['status'] ?? $payload['state'] ?? '';
$event = $payload['event'] ?? '';

// Check if payment successful
if ($status === 'SUCCESS' || $status === 'COMPLETED' || $event === 'payment.completed') {
    logWebhook("✅ SUCCESSFUL PAYMENT: $transactionId");
    
    // Save to valid transactions
    if (!empty($transactionId)) {
        // Save to JSON file
        $validFile = 'valid-transactions.json';
        $transactions = [];
        if (file_exists($validFile)) {
            $content = file_get_contents($validFile);
            $transactions = json_decode($content, true) ?: [];
        }
        
        if (!in_array($transactionId, $transactions)) {
            $transactions[] = $transactionId;
            file_put_contents($validFile, json_encode($transactions));
            logWebhook("Saved transaction: $transactionId");
        }
        
        // Also save to text file as backup
        file_put_contents('valid_transactions.txt', $transactionId . "\n", FILE_APPEND);
    }
    
    // Send email notification
    $to = 'officialvideofunds@gmail.com';
    $subject = "✅ Payment Received - $transactionId";
    $message = "Transaction ID: $transactionId\nAmount: " . ($payload['amount'] ?? '62500') . " TZS\nTime: " . date('Y-m-d H:i:s');
    mail($to, $subject, $message);
    
    echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
} else {
    logWebhook("⚠️ Unknown status: $status");
    echo json_encode(['status' => 'received', 'message' => 'Webhook received']);
}
?>