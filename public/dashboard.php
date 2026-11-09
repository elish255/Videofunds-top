<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'firebase_config.php';
require_once __DIR__ . '/account_guard.php';

$uid   = $_SESSION['user_id'];
$token = $_SESSION['id_token'];

$firestoreUrl = "https://firestore.googleapis.com/v1/projects/" . FIRESTORE_PROJECT_ID . "/databases/(default)/documents/users/$uid";

function firestore_request($url, $method = 'GET', $token = null, $data = null) {
    $headers = ['User-Agent: VideoFundsApp/1.0'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    if ($data) $headers[] = 'Content-Type: application/json';
    $options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 30
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ];
    if ($data) $options['http']['content'] = json_encode($data);
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $httpCode = 0;
    if (isset($http_response_header[0])) {
        preg_match('/HTTP\/\d+\.\d+\s+(\d+)/', $http_response_header[0], $matches);
        $httpCode = (int)$matches[1];
    }
    return ['response' => $response, 'http_code' => $httpCode];
}

// Fetch user document
$result = firestore_request($firestoreUrl, 'GET', $token);
$userData = json_decode($result['response'], true);

if ($result['http_code'] === 404) {
    $defaultData = [
        'fields' => [
            'fullname'       => ['stringValue' => $_SESSION['user_email'] ?: 'User_' . substr($uid, 0, 6)],
            'email'          => ['stringValue' => $_SESSION['user_email']],
            'balance'        => ['doubleValue' => 5.00],
            'referral_code'  => ['stringValue' => substr($uid, 0, 8)],
            'referred_by'    => ['stringValue' => ''],
            'country_code'   => ['stringValue' => 'TZ'],
            'currency_code'  => ['stringValue' => 'TZS'],
            'exchange_rate'  => ['doubleValue' => 2600.0],
            'created_at'     => ['timestampValue' => date('c')],
            'daily_watch_count' => ['integerValue' => 0],
            'last_watch_date'   => ['stringValue' => date('Y-m-d')],
            'referral_bonus_claimed' => ['booleanValue' => false],
            'activated'      => ['booleanValue' => false]
        ]
    ];
    firestore_request($firestoreUrl, 'PUT', $token, $defaultData);
    $result = firestore_request($firestoreUrl, 'GET', $token);
    $userData = json_decode($result['response'], true);
}

// Extract fields
if ($userData && isset($userData['fields'])) {
    $fullname = $userData['fields']['fullname']['stringValue'] ?? $_SESSION['user_email'] ?? 'User';
    $balance  = $userData['fields']['balance']['doubleValue'] ?? 0;
    $referral = $userData['fields']['referral_code']['stringValue'] ?? '';
    $referred_by = $userData['fields']['referred_by']['stringValue'] ?? '';
    $country  = $userData['fields']['country_code']['stringValue'] ?? 'TZ';
    $currency = $userData['fields']['currency_code']['stringValue'] ?? 'TZS';
    $rate     = $userData['fields']['exchange_rate']['doubleValue'] ?? 2600;
    $dailyCount = $userData['fields']['daily_watch_count']['integerValue'] ?? 0;
    $lastDate   = $userData['fields']['last_watch_date']['stringValue'] ?? '';
    $today = date('Y-m-d');
    $displayDaily = ($lastDate === $today) ? $dailyCount : 0;
    $remainingToday = max(0, 50 - $displayDaily);
    // READ ACTIVATED FIELD
    $activated = isset($userData['fields']['activated']) ? $userData['fields']['activated']['booleanValue'] : false;
} else {
    $fullname = $_SESSION['user_email'] ?? 'User';
    $balance  = 5.00;
    $referral = '';
    $referred_by = '';
    $country  = 'TZ';
    $currency = 'TZS';
    $rate     = 2600;
    $displayDaily = 0;
    $remainingToday = 50;
    $activated = false;
}

// Debug line - will appear in page source
echo "<!-- Activation status: " . ($activated ? "TRUE" : "FALSE") . " -->";

// Ensure user has a referral code
if (empty($referral)) {
    $referral = substr($uid, 0, 8);
    $updateData = ['fields' => ['referral_code' => ['stringValue' => $referral]]];
    $updateUrl = $firestoreUrl . '?updateMask.fieldPaths=referral_code';
    firestore_request($updateUrl, 'PATCH', $token, $updateData);
}

if ($country === 'TZ' && $currency === 'USD') {
    $currency = 'TZS';
    $rate = 2600;
}

$currencySymbols = ['USD'=>'$','TZS'=>'TSh','KES'=>'KSh','EUR'=>'€','GBP'=>'£','NGN'=>'₦','INR'=>'₹'];
$localSymbol = $currencySymbols[$currency] ?? $currency;

// ========== REFERRAL BONUS CLAIMING (ATOMIC FIRESTORE COMMIT) ==========
$bonusMessage = '';
$referralEarningsTotal = 0.00;
$newBalance = $balance;
$pendingUrl = "https://firestore.googleapis.com/v1/projects/" . FIRESTORE_PROJECT_ID . "/databases/(default)/documents/users/$uid/pending_bonuses";
$pendingResult = firestore_request($pendingUrl, 'GET', $token);
$pendingData = json_decode($pendingResult['response'], true);
$bonusIds = [];
$totalBonus = 0.00;
$unclaimedDocs = [];
if (isset($pendingData['documents'])) {
    $referralCount = count($pendingData['documents']);
    foreach ($pendingData['documents'] as $doc) {
        $fields = $doc['fields'] ?? [];
        $claimed = $fields['claimed']['booleanValue'] ?? false;
        $amount = (float)($fields['amount']['doubleValue'] ?? 2.00);
        if (!$claimed && $amount > 0) { $totalBonus += $amount; $unclaimedDocs[] = $doc; }
        elseif ($claimed) { $referralEarningsTotal += $amount; }
    }
    if ($totalBonus > 0 && !empty($userData['updateTime'])) {
        try {
            require_once __DIR__ . '/admin/_lib.php';
            $writes = [];
            foreach ($unclaimedDocs as $doc) {
                $writes[] = [
                    'update' => ['name' => $doc['name'], 'fields' => ['claimed' => ['booleanValue' => true]]],
                    'updateMask' => ['fieldPaths' => ['claimed']],
                    'currentDocument' => ['updateTime' => $doc['updateTime']]
                ];
            }
            $writes[] = [
                'update' => ['name' => $userData['name'], 'fields' => ['balance' => ['doubleValue' => $balance + $totalBonus]]],
                'updateMask' => ['fieldPaths' => ['balance']],
                'currentDocument' => ['updateTime' => $userData['updateTime']]
            ];
            $commitUrl = 'https://firestore.googleapis.com/v1/projects/' . rawurlencode(FIRESTORE_PROJECT_ID) . '/databases/(default)/documents:commit';
            $ch = curl_init($commitUrl);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode(['writes' => $writes]), CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . access_token()], CURLOPT_TIMEOUT => 25]);
            $commitResponse = curl_exec($ch); $commitCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
            if ($commitCode >= 200 && $commitCode < 300) {
                $balance += $totalBonus; $newBalance = $balance; $referralEarningsTotal += $totalBonus;
                $bonusMessage = "🎉 You earned $" . number_format($totalBonus, 2) . " from referral bonuses!";
            } else {
                error_log('Referral bonus commit not applied (likely concurrent claim): ' . $commitResponse);
            }
        } catch (Throwable $e) { error_log('Referral bonus claim error: ' . $e->getMessage()); }
    }
} else { $referralCount = 0; }

// Get referrer name
$referrerDisplay = 'None';
if (!empty($referred_by)) {
    $queryUrl = "https://firestore.googleapis.com/v1/projects/" . FIRESTORE_PROJECT_ID . "/databases/(default)/documents:runQuery";
    $queryData = [
        'structuredQuery' => [
            'from' => [['collectionId' => 'users']],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => 'referral_code'],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $referred_by]
                ]
            ],
            'limit' => 1
        ]
    ];
    $ch = curl_init($queryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($queryData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $resp = curl_exec($ch);
    curl_close($ch);
    $refResults = json_decode($resp, true);
    if (!empty($refResults) && isset($refResults[0]['document'])) {
        $refFields = $refResults[0]['document']['fields'];
        $refName = $refFields['fullname']['stringValue'] ?? $referred_by;
        $referrerDisplay = htmlspecialchars($refName);
    } else {
        $referrerDisplay = htmlspecialchars($referred_by);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User account - VideoFunds</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font.css">
    <link rel="stylesheet" href="css/main.css?rstr=532909801696">
    <link href="css/business-frontpage.css?rstr=532909801696" rel="stylesheet">
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/popper.min.js"></script>
    <script type="text/javascript" src="js/commonscripts.js"></script>
    <script type="text/javascript" src="js/geterrorcodes.js?new"></script>
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
<script type="text/javascript" src="js/lan/lan.php"></script>
</body>
</html>