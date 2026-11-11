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
@media(max-width:575px){.flow-content{margin-top:0;padding:0 10px}.flow-content h1{font-size:19px}.flow-content .message{font-size:13px}}
</style>
</head>
<body>
<nav class="navbar navbar-expand navbar-dark">
 <a class="navbar-brand" href="index.php">Videofunds</a>
 <div class="ml-auto"><a class="nav-link" href="dashboard.php">◉ User area</a></div>
</nav>
