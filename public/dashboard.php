<?php
require_once __DIR__.'/supabase.php';
$user=sb_require_user();
$uid=(string)$user['id'];
$profile=sb_profile($uid);
if(!$profile){
  $email=(string)($user['email']??'');
  $profile=['id'=>$uid,'full_name'=>(string)($user['user_metadata']['full_name']??'User'),'email'=>$email,'balance'=>5.00,'referral_code'=>strtoupper(substr(bin2hex(random_bytes(5)),0,8)),'referred_by'=>null,'country_code'=>'TZ','currency_code'=>'USD','exchange_rate'=>1,'daily_watch_count'=>0,'last_watch_date'=>date('Y-m-d'),'activated'=>false,'banned'=>false];
  sb_request('POST','/rest/v1/vf_profiles',$profile,true,null,['Prefer: return=minimal']);
}
$profile = vf_sync_profile_currency($uid, $profile);
$notifications = [];
try {
    $accessToken = (string)($_COOKIE['vf_access_token'] ?? '');
    if ($accessToken !== '') {
        $notificationResponse = sb_request(
            'GET',
            '/rest/v1/vf_notifications?select=id,title,body,is_read,created_at&user_id=eq.'.rawurlencode($uid).'&order=created_at.desc&limit=20',
            null,
            false,
            $accessToken
        );
        $notifications = is_array($notificationResponse['data'] ?? null) ? $notificationResponse['data'] : [];
    }
} catch (Throwable $e) {
    error_log('VideoFunds notifications: '.$e->getMessage());
}
$unreadNotifications = count(array_filter($notifications, static fn($n) => empty($n['is_read'])));

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
$isAdmin = !empty($profile['is_admin']);
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
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
    <style>
        .valname { font-size: 18px; font-weight: bold; color: #533651; }
        .valval { font-size: 22px; font-weight: bold; color: blue; }
        .btn-watch, .btn-withdraw { font-size: 1.05rem; padding: 12px 14px; }
        .navbar { z-index: 1030; }
        .navbar .nav-link { display:flex; align-items:center; gap:8px; padding:10px 12px; border-radius:8px; transition:background-color .18s ease,color .18s ease; }
        .navbar .nav-link:hover, .navbar .nav-link:focus { background:#814B5E; color:#fff !important; text-decoration:none; }
        .navbar-toggler { border:1px solid rgba(255,255,255,.55); border-radius:8px; padding:7px 10px; outline:none !important; }
        .navbar-toggler .fa-bars { color:#fff; }
        .navbar-brand { white-space:nowrap; }
        .vf-notifications { max-width:900px; margin:12px auto 18px; text-align:left; }
        .vf-notification { position:relative; background:#fff; border:1px solid #e3e8ef; border-left:5px solid #9aa7b5; border-radius:10px; padding:13px 15px; margin:9px 0; box-shadow:0 3px 12px rgba(20,35,55,.06); }
        .vf-notification.unread { border-left-color:#d8324b; background:#fffafb; }
        .vf-notification-head { display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
        .vf-notification-title { color:#26364a; font-weight:700; margin:0; flex:1; min-width:150px; }
        .vf-unread-dot { width:9px; height:9px; display:inline-block; border-radius:50%; background:#dc3545; flex:0 0 9px; }
        .vf-unread-count { display:inline-block; margin-left:7px; padding:2px 8px; border-radius:20px; background:#dc3545; color:white; font-size:12px; vertical-align:middle; }
        .vf-notification-body { white-space:pre-wrap; overflow-wrap:anywhere; color:#39485a; margin:12px 0 0; }
        .vf-notification-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
        .vf-notification-actions button { margin:0; }
        .vf-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:38px; padding:8px 13px; border:0; border-radius:7px; color:#fff; cursor:pointer; text-decoration:none; transition:background-color .18s ease, box-shadow .18s ease; }
        .vf-btn:hover,.vf-btn:focus { color:#fff; text-decoration:none; filter:brightness(.92); box-shadow:0 2px 7px rgba(0,0,0,.16); }
        .vf-btn-read { background:#1767c5; } .vf-btn-dismiss { background:#64748b; }
        .vf-empty-notifications { color:#68778a; background:#f7f9fc; border-radius:9px; padding:13px; }
        @media (max-width: 991.98px) {
            .navbar-collapse { padding:10px 0 14px; }
            .navbar-nav { gap:3px; }
            .navbar .nav-link { padding:11px 13px; }
        }
        @media (max-width: 575.98px) {
            body { padding-top:72px; }
            header .font9 { font-size:1.55rem !important; }
            .btn-watch,.btn-withdraw { width:100% !important; font-size:1rem; }
            .vf-notification { padding:12px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./"><span class="font9" style="font-weight:bold;font-size:27px;color:#fff;">Videofunds</span></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Fungua menyu">
            <i class="fa fa-bars" aria-hidden="true"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="./"><i class="fa fa-home" aria-hidden="true"></i> Main</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa fa-user-circle" aria-hidden="true"></i> User Area</a></li>
                <li class="nav-item"><a class="nav-link" href="leaderboard.php"><i class="fa fa-trophy" aria-hidden="true"></i> Leaderboard</a></li>
                <?php if ($isAdmin): ?><li class="nav-item"><a class="nav-link" href="/admin/" style="font-weight:bold;color:#ffd700;"><i class="fa fa-user-shield" aria-hidden="true"></i> Admin Panel</a></li><?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out-alt" aria-hidden="true"></i> Logout</a></li>
                <li class="nav-item"><a class="nav-link" href="#" onclick="return false;"><i class="fa fa-question-circle" aria-hidden="true"></i> Support</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php if (!empty($notifications)): ?>
<section class="container vf-notifications" aria-label="Notifications">
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
        <strong><i class="fa fa-bell" aria-hidden="true"></i> Notifications
            <?php if ($unreadNotifications > 0): ?><span class="vf-unread-count"><?= (int)$unreadNotifications ?> unread</span><?php endif; ?>
        </strong>
    </div>
    <?php foreach ($notifications as $notification): ?>
        <article class="vf-notification <?= empty($notification['is_read']) ? 'unread' : '' ?>" data-notification-id="<?= (int)$notification['id'] ?>">
            <div class="vf-notification-head">
                <?php if (empty($notification['is_read'])): ?><span class="vf-unread-dot" aria-label="Unread"></span><?php endif; ?>
                <h3 class="vf-notification-title"><?= htmlspecialchars((string)$notification['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <small class="text-muted"><?= htmlspecialchars(date('d M Y, H:i', strtotime((string)$notification['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
            </div>
            <div class="vf-notification-body" hidden><?= nl2br(htmlspecialchars((string)$notification['body'], ENT_QUOTES, 'UTF-8')) ?></div>
            <div class="vf-notification-actions">
                <button type="button" class="vf-btn vf-btn-read js-open-notification"><i class="fa fa-book-open" aria-hidden="true"></i> Fungua / Soma</button>
                <button type="button" class="vf-btn vf-btn-dismiss js-dismiss-notification" hidden><i class="fa fa-times" aria-hidden="true"></i> Dismiss</button>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php endif; ?>

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

    document.querySelectorAll('.vf-notification').forEach(function(card) {
        const id = card.dataset.notificationId;
        const body = card.querySelector('.vf-notification-body');
        const openButton = card.querySelector('.js-open-notification');
        const dismissButton = card.querySelector('.js-dismiss-notification');
        openButton.addEventListener('click', async function() {
            if (body.hidden) {
                body.hidden = false;
                openButton.innerHTML = '<i class="fa fa-eye-slash" aria-hidden="true"></i> Funga ujumbe';
                dismissButton.hidden = false;
                if (card.classList.contains('unread')) {
                    try {
                        const response = await fetch('notification-action.php', {
                            method: 'POST',
                            headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
                            credentials: 'same-origin',
                            body: new URLSearchParams({action:'mark_read', id:id})
                        });
                        const result = await response.json();
                        if (!response.ok || !result.success) throw new Error(result.message || 'Imeshindikana kusasisha notification');
                        card.classList.remove('unread');
                        card.querySelector('.vf-unread-dot')?.remove();
                        const count = document.querySelector('.vf-unread-count');
                        if (count) {
                            const remaining = Math.max(0, parseInt(count.textContent, 10) - 1);
                            if (remaining) count.textContent = remaining + ' unread';
                            else count.remove();
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }
            } else {
                body.hidden = true;
                openButton.innerHTML = '<i class="fa fa-book-open" aria-hidden="true"></i> Fungua / Soma';
            }
        });
        dismissButton.addEventListener('click', async function() {
            if (!confirm('Unataka kuondoa notification hii?')) return;
            dismissButton.disabled = true;
            try {
                const response = await fetch('notification-action.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
                    credentials: 'same-origin',
                    body: new URLSearchParams({action:'dismiss', id:id})
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'Imeshindikana kuondoa notification');
                card.remove();
                if (!document.querySelector('.vf-notification')) {
                    const section = document.querySelector('.vf-notifications');
                    if (section) section.remove();
                }
            } catch (error) {
                alert(error.message);
                dismissButton.disabled = false;
            }
        });
    });
</script>
<?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>