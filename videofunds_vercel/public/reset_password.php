<?php
session_start();
require_once 'firebase_config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Firebase REST API to send password reset email
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=' . FIREBASE_API_KEY;
        $data = [
            'requestType' => 'PASSWORD_RESET',
            'email' => $email
        ];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
                'ignore_errors' => true,
                'timeout' => 30
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            $error = $result['error']['message'];
            if ($error === 'EMAIL_NOT_FOUND') {
                $error = 'No account found with that email address.';
            } else {
                $error = 'Unable to send reset email: ' . $error;
            }
        } else {
            $message = 'Password reset email has been sent to ' . htmlspecialchars($email) . '. Please check your inbox (and spam folder).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VideoFunds</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('background.png');
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
            padding-top: 100px;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.55);
            z-index: 0;
        }
        .reset-container {
            max-width: 450px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            border-radius: 26px;
            padding: 30px 25px;
            position: relative;
            z-index: 2;
            box-shadow: 0 16px 32px rgba(0,0,0,0.25);
        }
        h2 { color: #18610A; text-align: center; margin-bottom: 20px; }
        .btn-reset { background: #c92121; width: 100%; }
        .btn-reset:hover { background: #a51a1a; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #2563eb; }
    </style>
</head>
<body>
<div class="reset-container">
    <h2><i class="fas fa-key"></i> Reset Password</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn btn-primary">Return to Login</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label><i class="far fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="your@email.com">
            </div>
            <button type="submit" class="btn btn-reset"><i class="fas fa-paper-plane"></i> Send Reset Email</button>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>