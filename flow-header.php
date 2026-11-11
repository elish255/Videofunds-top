<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'VideoFunds';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="css/font.css">
<link rel="stylesheet" href="css/main.css">
<style>
html,body{min-height:100%;margin:0}
body{font-family:Arial,sans-serif;background:#fff url('background.png') center top/cover fixed no-repeat;color:#171717;display:flex;flex-direction:column}
.navbar{background:#68162e!important;min-height:40px;padding:.2rem .8rem;box-shadow:0 2px 8px rgba(0,0,0,.16)}
.navbar-brand{color:#1686ff!important;font-weight:700;font-size:16px;padding:0}
.navbar .nav-link{color:#fff!important;font-size:12px;padding:.35rem .55rem}
.flow-content{width:100%;max-width:900px;margin:40px auto 0;padding:0 12px;text-align:center;flex:0 0 auto}
.flow-content h1{font-size:21px;line-height:1.35;color:#176327;font-weight:700;margin:0 0 5px}
.flow-content .dear{font-size:14px;font-weight:700;color:#071dba;margin:5px 0}
.flow-content .message{font-size:14px;line-height:1.65;font-weight:700;color:#071dba;margin:8px auto;max-width:780px}
.flow-content .amount{font-size:21px;font-weight:800;color:#176327;margin:10px 0}
.flow-content .phone-note{font-size:14px;font-weight:700;color:#071dba;margin:7px 0}
.flow-action{display:inline-block;border:0;border-radius:0;background:#dc3545;color:#171717!important;text-decoration:none!important;padding:8px 16px;font-size:12px;min-width:124px}
.flow-action:hover{background:#c82333;color:#fff!important}
.flow-content .subnote{font-size:12px;color:#333;margin:9px auto;max-width:700px}
.vf-site-footer{margin-top:12px!important;padding:22px 14px!important}
.vf-balance{display:flex;flex-direction:column;align-items:center;gap:2px;margin:0 0 14px}
.vf-balance-label{font-size:18px;font-weight:700;color:#533651}
.vf-balance-value{font-size:22px;font-weight:700;color:blue}
.flow-card{background:rgba(255,255,255,.96);max-width:440px;margin:14px auto;padding:22px 22px 26px;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,.12);text-align:left}
.flow-card label{display:block;font-weight:700;font-size:14px;margin:14px 0 5px;color:#171717}
.flow-card input[type=text],.flow-card input[type=tel],.flow-card input[type=email]{width:100%;padding:10px;font-size:16px;border:1px solid #ccc;border-radius:5px;background:#fff}
.flow-card input[readonly]{background:#f1f3f6;color:#333}
.flow-card .hint{font-size:12px;color:#555;margin-top:5px}
.flow-btn{display:block;width:100%;margin-top:22px;padding:12px;background:#149210;color:#fff!important;font-size:17px;border:0;border-radius:5px;cursor:pointer;text-align:center;text-decoration:none!important}
.flow-btn:hover{background:#0e6e0c}
.flow-btn.red{background:#dc3545}.flow-btn.red:hover{background:#c82333}
.flow-btn.ghost{background:#6c757d}.flow-btn.ghost:hover{background:#565e64}
.flow-error{background:#fdecee;border:1px solid #f1aeb5;color:#842029;border-radius:6px;padding:9px 12px;margin:12px auto;max-width:440px;font-size:14px;font-weight:700}
.flow-link{display:inline-block;margin-top:12px;font-size:13px;color:#68162e;font-weight:700}
.flow-modal{display:none;position:fixed;z-index:2000;inset:0;overflow:auto;background:rgba(0,0,0,.6)}
.flow-modal.show{display:block}
.flow-modal-content{background:#fff;margin:14vh auto;padding:28px;width:88%;max-width:500px;text-align:center;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
.flow-modal-content h3{color:#dc3545;margin:0 0 12px;font-size:21px}
.flow-modal-content p{font-size:15px;line-height:1.6;color:#171717;text-align:left}
.flow-modal-content .flow-btn{display:inline-block;width:auto;min-width:140px;margin-top:14px}
@media(max-width:575px){.flow-content{margin-top:0;padding:0 10px}.flow-content h1{font-size:19px}.flow-content .message{font-size:13px}}
</style>
</head>
<body>
<nav class="navbar navbar-expand navbar-dark">
 <a class="navbar-brand" href="index.php">Videofunds</a>
 <div class="ml-auto"><a class="nav-link" href="dashboard.php">◉ User area</a></div>
</nav>
