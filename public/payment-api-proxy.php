<?php
session_start();
if (empty($_SESSION['user_id'])) { http_response_code(401); header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>'Login required']); exit; }
// payment-api-proxy.php - Secure backend proxy for API calls
// IMPORTANT: Place this outside your public web root if possible

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// YOUR API KEYS (Store these securely, never in frontend code)
define('ACCESS_KEY', getenv('PAYMENT_ACCESS_KEY') ?: '');
define('SECRET_KEY', getenv('PAYMENT_SECRET_KEY') ?: '');

// Log file for debugging
define('LOG_FILE', 'payment-logs.txt');

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$timestamp] $message\n", FILE_APPEND);
}

// Only accept POST requests for payment initiation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

$action = $input['action'] ?? '';

// Route to appropriate function
switch($action) {
    case 'initiate_payment':
        initiatePayment($input);
        break;
    case 'check_status':
        checkPaymentStatus($input);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}

function initiatePayment($data) {
    logMessage("Initiating payment for phone: " . ($data['phone_number'] ?? 'unknown'));
    
    // Validate required fields
    $phoneNumber = $data['phone_number'] ?? '';
    $amount = $data['amount'] ?? 62500;
    
    if (empty($phoneNumber)) {
        echo json_encode(['success' => false, 'message' => 'Phone number required']);
        return;
    }
    
    // Generate unique transaction reference
    $transactionRef = 'VID_' . time() . '_' . rand(1000, 9999);
    $orderId = 'ORD_' . time() . '_' . rand(1000, 9999);
    
    // Prepare payment data for the API
    // NOTE: You need to replace this with your actual payment provider's API endpoint and format
    $paymentData = [
        'access_key' => ACCESS_KEY,
        'secret_key' => SECRET_KEY,
        'phone_number' => $phoneNumber,
        'amount' => $amount,
        'currency' => 'TZS',
        'transaction_ref' => $transactionRef,
        'order_id' => $orderId,
        'merchant_id' => (getenv('PAYMENT_MERCHANT_ID') ?: ''),
        'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment-webhook.php',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Store transaction in database/file for tracking
    storeTransaction($transactionRef, $orderId, $phoneNumber, $amount, 'pending');
    
    // Make API call to your payment provider
    // REPLACE THIS URL WITH YOUR ACTUAL PAYMENT API ENDPOINT
    $apiUrl = getenv('PAYMENT_API_URL') ?: '';
    if (!$apiUrl || !ACCESS_KEY || !SECRET_KEY) { http_response_code(503); echo json_encode(['success'=>false,'message'=>'Payment provider credentials and endpoint must be configured by the site owner.']); return; }
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . ACCESS_KEY
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        logMessage("CURL Error: $curlError");
        echo json_encode(['success' => false, 'message' => 'Connection error', 'error' => $curlError]);
        return;
    }
    
    logMessage("API Response: $response");
    
    // Parse response (adjust based on your provider's response format)
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && $result) {
        // Assuming successful response structure
        echo json_encode([
            'success' => true,
            'message' => 'Payment initiated. Please check your phone.',
            'transaction_id' => $result['transaction_id'] ?? $transactionRef,
            'order_id' => $orderId,
            'status' => 'pending'
        ]);
    } else {
        logMessage("Payment initiation failed. HTTP: $httpCode");
        echo json_encode([
            'success' => false, 
            'message' => 'Payment initiation failed. Please try again.',
            'error' => $result['message'] ?? 'Unknown error'
        ]);
    }
}

function checkPaymentStatus($data) {
    $transactionId = $data['transaction_id'] ?? '';
    
    if (empty($transactionId)) {
        echo json_encode(['success' => false, 'message' => 'Transaction ID required']);
        return;
    }
    
    // Check local storage first
    $status = getTransactionStatus($transactionId);
    
    // If still pending, you might want to query the payment provider
    if ($status === 'pending') {
        // Make API call to check status
        // REPLACE WITH YOUR PROVIDER'S STATUS CHECK ENDPOINT
        $apiUrl = getenv('PAYMENT_STATUS_URL') ?: '';
        if (!$apiUrl || !ACCESS_KEY || !SECRET_KEY) { http_response_code(503); echo json_encode(['success'=>false,'message'=>'Payment status endpoint is not configured.']); return; }
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'access_key' => ACCESS_KEY,
            'secret_key' => SECRET_KEY,
            'transaction_id' => $transactionId
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && $result['status'] === 'completed') {
            $status = 'completed';
            updateTransactionStatus($transactionId, 'completed');
        }
    }
    
    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status === 'completed' ? 'Payment successful' : 'Payment pending or failed'
    ]);
}

// Helper functions for transaction storage
function storeTransaction($transactionId, $orderId, $phoneNumber, $amount, $status) {
    $transactionsFile = 'transactions.json';
    $transactions = [];
    
    if (file_exists($transactionsFile)) {
        $transactions = json_decode(file_get_contents($transactionsFile), true);
    }
    
    $transactions[$transactionId] = [
        'order_id' => $orderId,
        'phone_number' => $phoneNumber,
        'amount' => $amount,
        'status' => $status,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($transactionsFile, json_encode($transactions));
    logMessage("Transaction stored: $transactionId - Status: $status");
}

function getTransactionStatus($transactionId) {
    $transactionsFile = 'transactions.json';
    if (file_exists($transactionsFile)) {
        $transactions = json_decode(file_get_contents($transactionsFile), true);
        return $transactions[$transactionId]['status'] ?? 'unknown';
    }
    return 'unknown';
}

function updateTransactionStatus($transactionId, $status) {
    $transactionsFile = 'transactions.json';
    if (file_exists($transactionsFile)) {
        $transactions = json_decode(file_get_contents($transactionsFile), true);
        if (isset($transactions[$transactionId])) {
            $transactions[$transactionId]['status'] = $status;
            $transactions[$transactionId]['completed_at'] = date('Y-m-d H:i:s');
            file_put_contents($transactionsFile, json_encode($transactions));
            logMessage("Transaction $transactionId updated to: $status");
            
            // If completed, add to valid transactions
            if ($status === 'completed') {
                addToValidTransactions($transactionId);
            }
        }
    }
}

function addToValidTransactions($transactionId) {
    $validFile = 'valid-transactions.json';
    $validTransactions = [];
    
    if (file_exists($validFile)) {
        $validTransactions = json_decode(file_get_contents($validFile), true);
    }
    
    if (!in_array($transactionId, $validTransactions)) {
        $validTransactions[] = $transactionId;
        file_put_contents($validFile, json_encode($validTransactions));
        logMessage("Added $transactionId to valid transactions");
    }
}
?>