<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PayPal Withdrawal - VideoFunds</title>
    <!-- CSS na JS za dashboard -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font.css">
    <link rel="stylesheet" href="css/main.css?rstr=532909801696">
    <link href="css/business-frontpage.css?rstr=532909801696" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <style>
        /* Styles za awali za ukurasa huu */
        :root{--primary-color:#68162e;--secondary-color:#c92121;--accent-color:blue;--success-color:#149210;}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{
            background-image:url(images/background.jpg);
            background-size:cover;
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
            padding-top: 80px; /* Kwa sababu navbar ni fixed-top */
        }
        .page-header{text-align:center;margin:2rem 0;color:#18610A;font-size:2rem;}
        .form-container{max-width:600px;margin:2rem auto;padding:2rem;background:rgba(255,255,255,0.95);border-radius:15px;}
        .form-title{text-align:center;font-size:1.5rem;font-weight:bold;color:var(--accent-color);margin-bottom:1.5rem;}
        .input-field{width:100%;max-width:300px;padding:0.8rem;margin:0.5rem auto;border:2px solid #e43011;border-radius:6px;display:block;}
        .balance-info{text-align:center;margin-bottom:1rem;background:#f0f0f0;padding:8px;border-radius:20px;width:fit-content;margin:0 auto 1rem auto;}
        .submit-button{background-color:var(--secondary-color);color:white;padding:0.8rem 1.5rem;border:none;border-radius:20px;font-weight:bold;cursor:pointer;display:block;margin:0 auto;width:200px;}
        .submit-button:hover{background-color:#a51a1a;}
        footer{background:rgba(0,0,0,0.8);color:white;text-align:center;padding:1.5rem;margin-top:2rem;}
        .whatsapp-button{position:fixed;left:20px;bottom:20px;background:#25D366;padding:12px;border-radius:50%;width:50px;height:50px;display:flex;align-items:center;justify-content:center;}
        .whatsapp-button img{width:24px;height:24px;}
        .email-button{position:fixed;bottom:20px;right:20px;background:darkred;color:white;padding:12px 20px;border-radius:5px;text-decoration:none;display:flex;align-items:center;}
        .email-button img{width:16px;height:16px;margin-right:8px;}
    </style>
</head>
<body>

<!-- NAVBAR KAMA YA DASHBOARD.PHP -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./">
            <font class="font9" style="font-weight:bold;font-size:30px;color:blue;">Videofunds</font>
        </a>
        <div class="d-lg-none d-md-block d-sm-block text-right">
            <a class="nav-link" href="dashboard.php"><div class="fa-address-card"> User area</div></a>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
            aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="dark-blue-text"><i class="fas fa-bars fa-1x"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="./"><div class="fa-home"> Main</div></a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><div class="fa-address-card"> User area</div></a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><div class="fa-sign-in"> Sign Out</div></a></li>
                <li class="nav-item"><a class="nav-link" href="https://wa.me/+639126610699"><div class="fa-question-circle"> Support</div></a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- MAUDHUI KUU -->
<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center">
            <h1 class="page-header">Withdraw to PayPal</h1>
        </div>
    </div>
</div>

<div class="form-container">
    <div class="form-title">🌐 Enter PayPal Email</div>
    <div class="balance-info">💰 Your Balance: $5.00 | Min: $200.00</div>
    <form method="POST">
        <input type="email" name="paypal_email" class="input-field" placeholder="your@paypal.com" required>
        <input type="number" step="0.01" name="amount" class="input-field" placeholder="Amount in USD" required>
        <button type="submit" class="submit-button">Withdrawal money</button>
            </form>
    <div style="text-align:center; margin-top:15px;"><a href="choosepayment.php" style="color:gray;">← Back to methods</a></div>
</div>

<?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>