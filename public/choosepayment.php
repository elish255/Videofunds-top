<?php
require_once __DIR__.'/supabase.php';
$user=sb_require_user();
$uid=(string)$user['id'];
$profile=sb_profile($uid);
$balance=(float)($profile['balance']??5.00);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Choose Withdrawal Method - VideoFunds</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font.css">
    <link rel="stylesheet" href="css/main.css?rstr=532909801696">
    <link href="css/business-frontpage.css?rstr=532909801696" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <style>
        :root { --primary-color:#68162e; --secondary-color:#c92121; --accent-color:blue; --success-color:#149210; }
        body { background-image:url(images/background.jpg); background-size:cover; background-attachment:fixed; }
        .page-header { text-align:center; margin:2rem 0; color:#18610A; font-size:2rem; text-shadow:2px 2px 4px rgba(0,0,0,0.5); }
        .form-container { 
            max-width:900px; 
            margin:2rem auto; 
            padding:2rem; 
            background: transparent; 
            border-radius:15px;
            backdrop-filter: blur(3px);
        }
        .payment-card { 
            transition:transform 0.2s; 
            cursor:pointer; 
            border:1px solid #ddd; 
            border-radius:10px; 
            padding:20px; 
            margin:15px; 
            text-align:center; 
            background:#fff; 
            display:inline-block; 
            width:250px; 
        }
        .payment-card:hover { transform:scale(1.02); box-shadow:0 10px 20px rgba(0,0,0,0.1); }
        .payment-img { max-width:100px; margin-bottom:15px; }
        .method-title { font-size:1.3rem; font-weight:bold; color:#18610A; }
        .balance-info { 
            text-align:center; 
            margin-bottom:1.5rem; 
            background: rgba(0,0,0,0.6); 
            padding:10px; 
            border-radius:20px; 
            width:fit-content; 
            margin-left:auto; 
            margin-right:auto;
            color: white;
            font-weight: bold;
        }
        .cancel-btn {
            background: rgba(128,128,128,0.8);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 20px;
            color: white;
            display: inline-block;
            transition: 0.2s;
        }
        .cancel-btn:hover {
            background: rgba(100,100,100,0.9);
            color: white;
            text-decoration: none;
        }
        footer { background:rgba(0,0,0,0.8); color:white; text-align:center; padding:1.5rem; margin-top:2rem; }
        body { padding-top: 80px; }
        @media (max-width:768px) { 
            .payment-card { width:90%; margin:10px auto; display:block; } 
        }
    </style>
</head>
<body>

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

<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center mt-3">
            <font class="font9" style="font-size:2rem;font-weight:bold;color:#18610A;">Choose Withdrawal Method</font>
        </div>
    </div>
</div>

<div class="form-container">
    <div class="balance-info">
        💰 Your Balance: $<?php echo number_format($balance, 2); ?> | 
        Min: $200.00
    </div>
    <div style="text-align:center;">
        <div class="payment-card" onclick="location.href='visacardnumber.php';">
            <img src="images/paysystems/en/card.png" class="payment-img"><br>
            <div class="method-title">Visa Card</div>
            <p>Withdrawal using card</p>
        </div>
        <div class="payment-card" onclick="location.href='enterphonenumber.php';">
            <img src="images/paysystems/en/phone.png" class="payment-img"><br>
            <div class="method-title">Phone Bill</div>
            <p>Withdrawal using phone bill</p>
        </div>
        <div class="payment-card" onclick="location.href='paypal.php';">
            <img src="images/paysystems/en/paypal.png" class="payment-img"><br>
            <div class="method-title">PayPal</div>
            <p>Withdrawal using PayPal</p>
        </div>
    </div>
    <div style="text-align:center; margin-top:20px;">
        <a href="dashboard.php" class="cancel-btn">Cancel</a>
    </div>
</div>

<footer>
    <div class="container">
        <p class="m-0 text-center text-white">Copyright &copy; videofunds.top 2007-2025</p>
    </div>
</footer>
</body>
</html>