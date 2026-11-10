<?php
require_once __DIR__.'/supabase.php';
$user=sb_require_user();
$uid=(string)$user['id'];
$profile=sb_profile($uid);
if(!$profile){
  $email=(string)($user['email']??'');
  $profile=['id'=>$uid,'full_name'=>(string)($user['user_metadata']['full_name']??'User'),'email'=>$email,'balance'=>5.00,'referral_code'=>strtoupper(substr(bin2hex(random_bytes(5)),0,8)),'referred_by'=>null,'country_code'=>'TZ','currency_code'=>'USD','exchange_rate'=>1,'daily_watch_count'=>0,'last_watch_date'=>date('Y-m-d'),'activated'=>false,'banned'=>false];
  sb_request('POST','/rest/v1/profiles',$profile,true,null,['Prefer: return=minimal']);
}
if(!empty($profile['banned'])){sb_clear_auth_cookies();header('Location: /index.php?error=account_disabled');exit;}
$fullname=$profile['full_name']?:($user['email']??'User');
$balance=(float)($profile['balance']??5.00);
$referral=(string)($profile['referral_code']??'');
$referred_by=(string)($profile['referred_by']??'');
$country=(string)($profile['country_code']??'TZ');
$currency=(string)($profile['currency_code']??'USD');
$rate=(float)($profile['exchange_rate']??1);
$displayDaily=((string)($profile['last_watch_date']??'')===date('Y-m-d'))?(int)($profile['daily_watch_count']??0):0;
$remainingToday=max(0,50-$displayDaily);
$activated=(bool)($profile['activated']??false);
echo '<!-- Supabase profile loaded -->';
$bonusMessage='';$referralEarningsTotal=0.00;$referralCount=0;$referrerDisplay=$referred_by!==''?htmlspecialchars($referred_by):'None';
$currencySymbols = ['USD'=>'$','TZS'=>'TSh','KES'=>'KSh','EUR'=>'€','GBP'=>'£','NGN'=>'₦','INR'=>'₹'];
$localSymbol = $currencySymbols[$currency] ?? $currency;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User account - VideoFunds</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font.css">
    <link rel="stylesheet" href="css/main.css?rstr=532909801696">
    <link href="css/business-frontpage.css?rstr=532909801696" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <style>
        .valname { font-size: 18px; font-weight: bold; color: #533651; }
        .valval { font-size: 22px; font-weight: bold; color: blue; }
        .btn-watch, .btn-withdraw { font-size: 1.2rem; padding: 10px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./"><font class="font9" style="font-weight:bold;font-size:30px;color:blue;">Videofunds</font></a>
        <div class="d-lg-none d-md-block d-sm-block text-right">
            <a class="nav-link" href="logout.php"><div class="fa-address-card"> Logout</div></a>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive">
            <span class="dark-blue-text"><i class="fas fa-bars fa-1x"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="./"><div class="fa-home"> Main</div></a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><div class="fa-address-card"> User area</div></a></li>
                <li class="nav-item"><a class="nav-link" href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><div class="fa-sign-in"> Sign Out</div></a></li>
                <li class="nav-item"><a class="nav-link" href="#"><div class="fa-question-circle"> Support</div></a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="py-1 mb-1 mt-3">
    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-12 mt-0 text-center">
                <font class="font9" style="font-size:2rem;font-weight:bold;color:#18610A;">User account <font color="blue"><?php echo htmlspecialchars($fullname); ?></font></font>
                <?php if ($bonusMessage): ?>
                    <div class="alert alert-success mt-2" style="max-width:500px; margin:10px auto;"><?php echo $bonusMessage; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label class="font9 valname">Earned money</label><br>
                <font class="font9 valval">$ <?php echo number_format($balance, 2); ?> &nbsp;&nbsp;(<?php echo $localSymbol . ' ' . number_format($balance * $rate, 2); ?>)</font><br>
                <table border="0" width="100%">
                    <tr>
                        <td width='50%'><a class="btn btn-primary btn-lg w-75 btn-withdraw" href="choosepayment.php">Withdraw money</a></td>
                        <td width='50%'><a class="btn btn-primary btn-lg w-75 btn-watch" href="watch-video.php">Watch paid videos</a></td>
                    </tr>
                </table>
                <hr>
            </div>

            <div class="form-group">
                <label class="font9 valname">Today's video watch progress</label><br>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($displayDaily / 50) * 100; ?>%;" aria-valuenow="<?php echo $displayDaily; ?>" aria-valuemin="0" aria-valuemax="50">
                        <?php echo $displayDaily; ?> / 50
                    </div>
                </div>
                <small><?php echo $remainingToday; ?> videos left today</small>
                <hr>
            </div>

            <!-- Activation button - changes based on Firestore -->
            <div class="form-group">
                <?php if ($activated === true): ?>
                    <a class="btn btn-success btn-lg w-75" href="#" style="background-color: #28a745; cursor: default; color: white;">✅ Activated</a>
                <?php else: ?>
                    <a class="btn btn-danger btn-lg w-75" href="#" style="background-color: #c92121; cursor: default; color: white;">❌ Not Activated</a>
                <?php endif; ?>
                <hr>
            </div>

            <div class="form-group">
                <a class="btn btn-primary btn-lg w-75" href="dashboard.php"> Get extra reward 2 </a>
                <hr>
            </div>

            <div class="form-group">
                <label class="font9 valname">Referrals attracted</label><br>
                <font class="font9 valval"><?php echo $referralCount; ?></font>
                <hr>
            </div>

            <div class="form-group">
                <label class="font9 valname">Earned by referrals</label><br>
                <font class="font9 valval">$ <?php echo number_format($referralEarningsTotal, 2); ?> &nbsp;&nbsp;(<?php echo $localSymbol . ' ' . number_format($referralEarningsTotal * $rate, 2); ?>)</font>
                <hr>
            </div>

            <div class="form-group">
                <label class="font9 valname">Referred by</label><br>
                <font class="font9 valval"><?php echo $referrerDisplay; ?></font>
                <hr>
            </div>

            <div class="form-group">
                <label class="font9 valname" style="font-weight:bold;">Link to attract referrals</label>
                <input type="text" name="reflink" id="reflink" class="form-control" value="https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?ref=<?php echo htmlspecialchars($referral); ?> - Sign up to get a $5 bonus!" onclick="javascript:doselect('reflink')" style="font-size:18px;font-weight:bold;color:green;">
                <button class="btn btn-secondary mt-2" id="copyBtn">Copy Link</button>
                <hr>
            </div>

        </div>
    </div>
</div>

<!-- Footer with share buttons -->
<div id='share_word' style="display:none">Join now to receive a $5 welcome bonus! I've earned over $200 just by watching videos here, so come on!</div>
<div style="height:140px;width:100%">
    <center>
        <div style="width:85%"><b>Invite friends to help you earn money and get an extra 40% of your friends income!</b></div>
        </table>
            <tr>
                <td><a href='javascript:share_wa()'><img src='images/wa.jpg' style="width:60px" border=0></a></td>
                <td><a href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2F<?php echo $_SERVER['HTTP_HOST']; ?>%2Findex.php%3Fref%3D<?php echo urlencode($referral); ?>&quote=Join now to receive a $5 welcome bonus! I've earned over $200 just by watching videos here, so come on!" target="_blank"><img src='images/fb.jpg' style="width:60px" border=0></a></td>
                <td><a href='javascript:share_ms()'><img src='images/ms.jpg' style="width:60px" border=0></a></td>
                <td><a href='javascript:share_tg()'><img src='images/tg.jpg' style="width:60px" border=0></a></td>
                <td><a href='javascript:share_twitter()'><img src='images/twitter.png' style="width:60px" border=0></a></td>
                <td><a href='javascript:share_line()'><img src='images/line.jpg' style="width:60px" border=0></a></td>
            </tr>
        </table>
        <div id='share'><label class="font9 valname" style="font-weight:bold;"><span style="color:#E53333;">Click the button above to share with your friends</span></label></div>
    </center>
</div>

<footer class="py-5 bg-dark">
    <div class="container">
        <p class="m-0 text-center text-white">Copyright &copy; videofunds.homes 2025-2045</p>
    </div>
</footer>

<script>
    function share_wa() { record('video','share_wa'); location.href = "whatsapp://send?text=" + document.getElementById('share_word').innerHTML + " " + "https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?ref=<?php echo urlencode($referral); ?>"; }
    function share_ms() { record('video','share_ms'); location.href = "fb-messenger://share/?link=" + encodeURIComponent("https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?ref=<?php echo urlencode($referral); ?>"); }
    function share_tg() { record('video','share_tg'); location.href = "tg://msg_url?url=" + document.getElementById('share_word').innerHTML + " " + "https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?ref=<?php echo urlencode($referral); ?>"; }
    function share_line() { record('video','share_line'); location.href = "line://msg/text/" + document.getElementById('share_word').innerHTML + " " + "https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?ref=<?php echo urlencode($referral); ?>"; }
    function share_twitter() { record('video','share_twitter'); location.href = "https://twitter.com/intent/tweet/?text=" + document.getElementById('share_word').innerHTML + "&url=" + "https://<?php echo $_SERVER['HTTP_HOST']; ?>/index.php?ref=<?php echo urlencode($referral); ?>"; }
    function doselect(id) { var copyText = document.getElementById(id); copyText.select(); copyText.setSelectionRange(0, 99999); document.execCommand("copy"); alert("Link copied to clipboard!"); }
    document.getElementById('copyBtn')?.addEventListener('click', function() { var copyText = document.getElementById('reflink'); copyText.select(); copyText.setSelectionRange(0, 99999); document.execCommand("copy"); alert("Referral link copied!"); });

    function rset_Cookie(name, value) { var Days = 30; var exp = new Date(); var domain = document.domain.split('.').slice(-2).join('.'); exp.setTime(exp.getTime() + (Days * 200 * 1000)); document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=."+domain+"; path=/;" }
    function rget_Cookie(name) { var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)"); if (arr = document.cookie.match(reg)) return unescape(arr[2]); return ''; }
    function record(a,b){ if(b){ if(rget_Cookie(a+b) !== a+b){ rset_Cookie(a+b,a+b); } } else { if(rget_Cookie(a) !== a){ rset_Cookie(a,a); } } }
    record('video');
</script>
</body>
</html>