<?php
// Dynamic meta data for better link previews
$site_url = "https://videofundtop.free.nf";
$page_title = "VideoFunds - Watch Videos & Earn Real Money";
$page_description = "Get paid to watch videos! Earn $0.60 for every minute you watch. Register now and get a $5 bonus.";
$og_image = "https://videofundtop.free.nf/preview.jpg"; // Make sure this image exists (1200x630px recommended)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="theme-color" content="#68162e">
    
    <title><?php echo $page_title; ?></title>
    <meta name="title" content="VideoFunds - Watch Videos & Earn Money">
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="video, earn money, watch videos, paid videos, online income">
    <meta name="author" content="VideoFunds">
    
    <!-- Open Graph / Facebook / WhatsApp / Telegram -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $site_url; ?>/index.php">
    <meta property="og:title" content="VideoFunds - Get Paid to Watch Videos | $5 Bonus">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:image" content="https://https://videofundtop.free.nf/videofunds.png">
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="VideoFunds - Earn real money watching videos">
    <meta property="og:site_name" content="VideoFunds">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card (large image summary) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="VideoFunds - Get Paid to Watch Videos">
    <meta name="twitter:description" content="<?php echo $page_description; ?>">
    <meta name="twitter:image" content="<?php echo $og_image; ?>">
    <meta name="twitter:site" content="@videofunds">
    
    <!-- Google Site Verification -->
    <meta name="google-site-verification" content="IE7_s20VEiqnchV1_r2L5IVXDB0UUO6Hk9RHf9S2m3c" />
    
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- Google Fonts + Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-image: url('background.png'); background-size: cover; background-position: center center; background-repeat: no-repeat; background-attachment: fixed; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0; position: relative; }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.55); z-index: 0; }
        .navbar { background-color: #68162e !important; box-shadow: 0 2px 8px rgba(0,0,0,0.2); padding: 0.5rem 1rem; }
        .navbar-brand { font-weight: bold; font-size: 1.6rem; color: #007bff !important; }
        .nav-link { color: white !important; font-weight: 500; font-size: 0.95rem; transition: 0.2s; }
        .nav-link i { margin-right: 4px; color: white !important; }
        .nav-link:hover { color: #d4d4d4 !important; }
        .navbar-toggler { border-color: rgba(255,255,255,0.6); }
        .navbar-toggler-icon { background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E"); }
        .app-wrapper { position: relative; z-index: 2; width: 100%; max-width: 480px; margin: 85px auto 80px auto; padding: 0 16px; }
        .card { background: rgba(255, 255, 255, 0.97); border-radius: 26px; padding: 20px 18px; box-shadow: 0 16px 32px rgba(0, 0, 0, 0.25); }
        .logo { text-align: center; font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; color: #007bff; }
        .tabs { display: flex; gap: 8px; margin-bottom: 18px; border-bottom: 1.5px solid #e5e7eb; }
        .tab { flex: 1; text-align: center; padding: 6px 4px; cursor: pointer; font-weight: 600; font-size: 0.85rem; color: #4b5563; }
        .tab.active { color: #2563eb; border-bottom: 2.5px solid #2563eb; margin-bottom: -1.5px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.8rem; color: #1f2937; }
        .form-group label i { width: 20px; }
        .form-group input { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 16px; font-size: 0.8rem; background-color: #fff; }
        .btn { width: 100%; padding: 10px 12px; background: linear-gradient(98deg, #2563eb, #4f46e5); color: white; border: none; border-radius: 32px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        #loginForm .btn, #registerForm .btn { background: linear-gradient(98deg, #dc2626, #b91c1c); }
        .error { background: #fee2e2; color: #b91c1c; padding: 8px 12px; border-radius: 24px; margin-bottom: 16px; border-left: 4px solid #dc2626; }
        .success { background: #dcfce7; color: #15803d; padding: 8px 12px; border-radius: 24px; margin-bottom: 16px; border-left: 4px solid #16a34a; }
        .form { display: none; }
        .form.active { display: block; }
        .country-info { background: #eef2ff; padding: 6px 10px; border-radius: 50px; margin-bottom: 16px; text-align: center; font-weight: 500; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .bottom-bar { position: fixed; bottom: 0; left: 0; width: 100%; background-color: #2c3e50; padding: 7px 16px; text-align: center; color: #ecf0f1; font-size: 0.7rem; z-index: 1000; }
        @media (max-width: 450px) { .card { padding: 16px 12px; } .app-wrapper { margin-top: 90px; } .navbar-brand { font-size: 1.3rem; } }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./">VideoFunds</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="./"><i class="fas fa-home"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-dollar-sign"></i> Earn</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-question-circle"></i> Support</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="app-wrapper">
    <div class="card">
        <div class="logo"><span>VideoFunds</span></div>
        <div class="country-info" id="countryInfoBox"><span id="countryFlag">🌍</span><span id="countryText">International</span>(<span id="currencyCode">USD</span>)</div>
        <div class="tabs"><div class="tab active" data-tab="login">Login</div><div class="tab" data-tab="register">Register & Get $5</div></div>
        
        <!-- Login Form -->
        <div id="loginForm" class="form active">
            <form method="POST" action="login.php">
                <div class="form-group"><label><i class="far fa-envelope"></i> Email Address</label><input type="email" name="email" required placeholder="your@gmail.com"></div>
                <div class="form-group"><label><i class="fas fa-lock"></i> Password</label><input type="password" name="password" required placeholder="••••••"></div>
                <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>
                <div style="text-align: center; margin-top: 10px;">
            <a href="reset_password.php" style="color: #2563eb; font-size: 0.75rem; text-decoration: none;">Forgot password?</a>
        </div>
            </form>
        </div>

        <!-- Register Form -->
        <div id="registerForm" class="form">
            <form method="POST" action="register.php" id="registerFormElement">
                <div class="form-group"><label><i class="fas fa-user"></i> Full Name</label><input type="text" name="fullname" required placeholder="John peter"></div>
                <div class="form-group"><label><i class="far fa-envelope"></i> Email Address</label><input type="email" name="email" required placeholder="your@gmail.com"></div>
                <div class="form-group"><label><i class="fas fa-key"></i> Password</label><input type="password" name="password" required placeholder="Create password"></div>
                <div class="form-group"><label><i class="fas fa-check-circle"></i> Confirm Password</label><input type="password" name="confirm_password" required placeholder="Repeat password"></div>
                <input type="hidden" name="referral_code" id="referral_code_input" value="">
                <input type="hidden" name="country_code" id="hiddenCountryCode" value="US">
                <input type="hidden" name="currency_code" id="hiddenCurrencyCode" value="USD">
                <input type="hidden" name="exchange_rate" id="hiddenExchangeRate" value="1.00">
                <button type="submit" class="btn"><i class="fas fa-gift"></i> Register & Get $5 Bonus</button>
            </form>
        </div>
    </div>
</div>

<div class="bottom-bar"><p><i class="fas fa-copyright"></i> 2025-2040 VideoFunds — Watch Videos & Earn Real Money</p></div>

<script>
    // Tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.dataset.tab;
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.form').forEach(f => f.classList.remove('active'));
            document.getElementById(tabName + 'Form').classList.add('active');
        });
    });

    // Referral code from URL
    function getRefCode() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('ref');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const ref = getRefCode();
        if (ref) {
            const refInput = document.getElementById('referral_code_input');
            if (refInput) refInput.value = ref;
            const msgDiv = document.createElement('div');
            msgDiv.style.backgroundColor = '#ffffcc';
            msgDiv.style.padding = '5px';
            msgDiv.style.margin = '10px 0';
            msgDiv.innerHTML = '🔗 Referral code detected: <strong>' + ref + '</strong>';
            document.querySelector('.card').prepend(msgDiv);
        }
    });

    // Keep the device reminder informational only. Registration success is decided by Supabase,
    // so a failed attempt or a reset database must not permanently block this browser.
    const registerFormElem = document.getElementById('registerFormElement');
    if (registerFormElem && new URLSearchParams(window.location.search).get('registered') === '1') {
        localStorage.setItem('videofunds_registered_device', 'true');
    }

    // Country info
    async function updateCountryInfo() {
        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();
            if (data && data.country_name) {
                document.getElementById('countryFlag').innerText = getFlagEmoji(data.country_code);
                document.getElementById('countryText').innerText = data.country_name;
                let currency = data.currency || 'USD';
                document.getElementById('currencyCode').innerText = currency;
                document.getElementById('hiddenCountryCode').value = data.country_code || 'US';
                document.getElementById('hiddenCurrencyCode').value = currency;
                let rate = 1.0;
                if (currency === 'EUR') rate = 0.92;
                if (currency === 'TZS') rate = 2600;
                if (currency === 'KES') rate = 129;
                document.getElementById('hiddenExchangeRate').value = rate;
            }
        } catch(e) { console.warn(e); }
    }
    function getFlagEmoji(code) {
        const flags = { US:'🇺🇸', GB:'🇬🇧', TZ:'🇹🇿', KE:'🇰🇪', NG:'🇳🇬', ZA:'🇿🇦', IN:'🇮🇳' };
        return flags[code] || '🌐';
    }
    updateCountryInfo();
</script>
</body>
</html>
