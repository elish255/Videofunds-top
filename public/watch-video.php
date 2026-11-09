<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'firebase_config.php';
require_once __DIR__ . '/account_guard.php';
$uid = $_SESSION['user_id'];
$token = $_SESSION['id_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Watch 70 Sec & Earn $3.40 - VideoFunds</title>
    <script src="js/jquery.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font.css">
    <link rel="stylesheet" href="css/main.css?rstr=532909801696">
    <link href="css/business-frontpage.css?rstr=532909801696" rel="stylesheet">
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/popper.min.js"></script>
    <script type="text/javascript" src="js/commonscripts.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing CSS – keep as is */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-image: url('background.png'); background-size: cover; background-position: center center; background-repeat: no-repeat; background-attachment: fixed; min-height: 100vh; position: relative; }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.55); z-index: 0; }
        .container { max-width: 1200px; margin: 0 auto 60px auto; padding: 15px; position: relative; z-index: 2; }
        .video-card { background: rgba(255,255,255,0.95); border-radius: 20px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .video-wrapper { position: relative; width: 100%; padding-bottom: 56.25%; background: #000; }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
        .video-info { padding: 20px; }
        .video-title { font-size: 18px; font-weight: 700; margin-bottom: 15px; display: flex; justify-content: space-between; color: #1f2937; }
        .daily-counter { background: #f3f4f6; padding: 10px 15px; border-radius: 16px; margin-bottom: 15px; text-align: center; font-size: 13px; font-weight: 600; }
        .daily-counter span { color: #2563eb; font-size: 16px; }
        .reward-box { background: linear-gradient(135deg,#f59e0b,#d97706); padding: 15px; border-radius: 16px; margin: 15px 0; text-align: center; color: white; }
        .reward-amount { font-size: 36px; font-weight: 800; margin: 5px 0; color: #ffd700; }
        .progress-container { background: #f3f4f6; padding: 15px; border-radius: 16px; margin: 15px 0; }
        .progress-bar { background: #e5e7eb; border-radius: 20px; height: 10px; overflow: hidden; margin: 12px 0; }
        .progress-fill { background: linear-gradient(90deg,#f59e0b,#ffd700); height: 100%; width: 0%; transition: width 0.3s; }
        .watch-status { text-align: center; font-size: 13px; padding: 8px; border-radius: 10px; background: #fef3c7; color: #92400e; }
        .timer { text-align: center; font-size: 24px; font-weight: 700; color: #d97706; margin: 10px 0; font-family: monospace; }
        .btn-next { background: linear-gradient(135deg,#2563eb,#4f46e5); color: white; padding: 12px 20px; border: none; border-radius: 40px; font-size: 14px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn-next:disabled { opacity: 0.6; cursor: not-allowed; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 24px; text-align: center; max-width: 350px; }
        .error-message { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 12px; margin-top: 15px; font-size: 13px; display: none; }
        footer { background: rgba(0,0,0,0.8); color: white; text-align: center; padding: 1.5rem; margin-top: 2rem; position: relative; z-index: 2; }
        @media (max-width: 550px) { .reward-amount { font-size: 28px; } .timer { font-size: 20px; } }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./"><font class="font9" style="font-weight:bold;font-size:30px;color:blue;">Videofunds</font></a>
        <div class="d-lg-none d-md-block d-sm-block text-right">
            <a class="nav-link" href="dashboard.php"><div class="fa-address-card"> User area</div></a>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive">
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

<div style="height: 85px;"></div>

<div class="container">
    <div class="video-card">
        <div class="video-wrapper">
            <iframe id="videoPlayer" src="https://www.youtube.com/embed/YtrFcxIXJXo?autoplay=1&enablejsapi=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="video-info">
            <div class="video-title">
                <span><i class="fab fa-youtube"></i> <span id="videoTitle">Tennis Match</span></span>
                <span class="video-count"><i class="fas fa-database"></i> 149 videos left (new only)</span>
            </div>
            <div class="daily-counter">
                📺 Today: <span id="watchedCount">0</span> / 50 (<span id="leftToday">50</span> left today)<br>
                🎯 Total unwatched: <span id="totalUnwatched">149</span>
            </div>
            <div class="reward-box">
                <div>💰 WATCH 70 SECONDS & EARN</div>
                <div class="reward-amount">+ $3.40 USD</div>
                <div>⭐ Each video can only be watched ONCE!</div>
            </div>
            <div class="progress-container">
                <div class="watch-status" id="watchStatus"><i class="fas fa-play-circle"></i> 🎬 Watch 70 seconds to earn $3.40!</div>
                <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                <div class="timer" id="timer">0 sec / 70 sec</div>
                <div class="countdown-msg" id="countdownMsg"></div>
            </div>
            <button id="nextBtn" class="btn-next" onclick="loadNextVideo()"><i class="fas fa-forward"></i> Next Random (Unwatched) Video</button>
            <div id="errorDisplay" class="error-message"></div>
        </div>
    </div>
</div>

<footer><div class="container"><p class="m-0 text-center text-white">Copyright &copy; videofunds.homes 2025-2045</p></div></footer>
<div id="successModal" class="modal"><div class="modal-content"><i class="fas fa-check-circle" style="color:#10b981; font-size:60px;"></i><h2>🎉 +$3.40 Added!</h2><p>Loading next new video...</p></div></div>

<script>
  const allVideos = [{"title":"Beautiful Waterfall","id":"6PuZJt-6ABM"},{"title":"Ocean Waves Relaxing","id":"TqIwb5xQBH0"},{"title":"Sunset Beach View","id":"YoGzbTuydco"},{"title":"Mountain Peak","id":"xXKPwAPNeqY"},{"title":"Forest River","id":"z8E4feJ2pPY"},{"title":"Rainy Day","id":"nYd0oPnFbA8"},{"title":"Snow Mountains","id":"lSX9fOSJ0KI"},{"title":"Desert Sands","id":"HYfwXa4bx_0"},{"title":"Green Fields","id":"ExbzSOsXoGo"},{"title":"Lake View","id":"2mSr6GgQV2Y"},{"title":"Northern Lights","id":"R5eefu7wjbw"},{"title":"Starry Night","id":"PIwG9z02hIk"},{"title":"Clouds Timelapse","id":"WVvIiXXLwAU"},{"title":"Volcano Eruption","id":"HKCQSroQbII"},{"title":"Aurora Borealis","id":"QnGcQcPUc18"},{"title":"Funny Cat Video","id":"YFGQJxpkPg4"},{"title":"Cute Puppy","id":"p000kE-nKeM"},{"title":"Funny Baby Laugh","id":"sMXCEn8fdtA"},{"title":"Funny Dog Tricks","id":"9wQJ3d05W44"},{"title":"Panda Playing","id":"KriBQVhsgZk"},{"title":"Monkey Funny","id":"QMfkSnwRuaY"},{"title":"Penguins Walking","id":"r1tmkM8CGo8"},{"title":"Cute Rabbit","id":"BwPA3npLdWM"},{"title":"Horse Running","id":"qLQa1qUsPWA"},{"title":"Eagle Flying","id":"iPqrSqDEnG0"},{"title":"Dolphin Swimming","id":"GoajqWs7SXA"},{"title":"Elephant Bath","id":"CMzctRZQIo0"},{"title":"Giraffe Eating","id":"948ngrlZ3K8"},{"title":"Zebra Running","id":"RuEQ2reWsAg"},{"title":"Lion Roaring","id":"aDW7sLXEDCE"},{"title":"Never Give Up","id":"swCxVutEB0Q"},{"title":"Stay Positive","id":"Fjl_iy7GbUE"},{"title":"Believe in Yourself","id":"O--lb2uPMHc"},{"title":"Success Mindset","id":"mzjnil0N3UE"},{"title":"Dream Big","id":"rqzxEejSKYw"},{"title":"Work Hard","id":"e9vrwKUDM7w"},{"title":"Stay Focused","id":"vPy8jIeob1I"},{"title":"You Can Do It","id":"8j1K3cFItDE"},{"title":"Make It Happen","id":"9VP7FtwH9Ng"},{"title":"No Excuses","id":"DrwtXCEP3-s"},{"title":"Paris France","id":"ez3atCZcENc"},{"title":"Tokyo Japan","id":"n9iFRac_1Rk"},{"title":"New York City","id":"jP9dwmilFcQ"},{"title":"Dubai City","id":"t2pooIWrbVk"},{"title":"London UK","id":"p9X7V0LKxwY"},{"title":"Rome Italy","id":"u1NlmFa0-68"},{"title":"Barcelona Spain","id":"weeI1G46q0o"},{"title":"Sydney Australia","id":"4r3TkdiMtcw"},{"title":"Rio Brazil","id":"zo7i8VTpfNM"},{"title":"Cape Town SA","id":"8bMh8azh3CY"},{"title":"Amazing Nature","id":"0HE25cgczEM"},{"title":"City Lights","id":"euuiJbG1jxs"},{"title":"Funny Goat","id":"dmFDjsypfEc"},{"title":"Baby Panda","id":"IvJBHu4SJdw"},{"title":"Fast Cars","id":"747pbjYasZU"},{"title":"Cooking Recipe","id":"V5prJNONRFs"},{"title":"Workout Motivation","id":"7hAy_6HfJWU"},{"title":"Meditation Music","id":"gXKy3GQJRKA"},{"title":"Wild Animals","id":"CMzctRZQIo0"},{"title":"Beautiful Birds","id":"X_4EJZ4aors"},{"title":"Sunset Timelapse","id":"wSEinBVa5zc"},{"title":"Snowfall","id":"VopB3HnOJQQ"},{"title":"Thunderstorm","id":"ZBrzANOssJY"},{"title":"Campfire Night","id":"_azHcO2nhG8"},{"title":"Underwater World","id":"W2fhICtNCkM"},{"title":"Space Galaxy","id":"DNeurw8SZgY"},{"title":"Funny Parrot","id":"Xb58fHszRec"},{"title":"Cute Hamster","id":"chJm1ygWPbg"},{"title":"Speeding Train","id":"qbVf5U7aPbQ"},{"title":"Roller Coaster","id":"ZUK4AoTAL5U"},{"title":"Dancing Robot","id":"geSRnhQpf58"},{"title":"Magic Trick","id":"O9r8CVfginU"},{"title":"Soccer Goal","id":"M1TNvuU7W2g"},{"title":"Basketball Dunk","id":"ZK77oFdxDZk"},{"title":"Tennis Match","id":"YtrFcxIXJXo"},{"title":"Swimming Race","id":"aE1pbN2PNSk"},{"title":"Mountain Bike","id":"wZPrrLve2Eo"},{"title":"Surfing Waves","id":"PEmI1_UlRwQ"},{"title":"Skydiving","id":"gR4Xuslczoo"},{"title":"Paragliding","id":"fCeUvPL9rMc"},{"title":"Hot Air Balloon","id":"EcHFvkft8Q4"},{"title":"Fireworks Show","id":"ZnN50b7aaxk"},{"title":"Rainbow after rain","id":"5RvuayBxTno"},{"title":"Flower Garden","id":"Otim2mDjsYM"},{"title":"Autumn Leaves","id":"Zn6scKf7k_0"},{"title":"Winter Wonderland","id":"GT7ZdxiS28A"},{"title":"Summer Beach","id":"qVqDnSjjklQ"},{"title":"Desert Oasis","id":"r0vBkQgd5h0"},{"title":"Iceberg Glacier","id":"J6Ga4wciA2k"},{"title":"Volcano Lava","id":"g9ch8yHthP4"},{"title":"Earthquake Simulation","id":"z7do1hhb6fE"},{"title":"Tornado Alley","id":"Bh-AUzp6WYM"},{"title":"Hurricane Wind","id":"fLXC_-UWiI0"},{"title":"Solar Eclipse","id":"0q7c3Jac56A"},{"title":"Lunar Eclipse","id":"ZY6nVImqfSM"},{"title":"Meteor Shower","id":"z8aBZZnv6y8"},{"title":"Comet Passing","id":"wtQ-aP1V7YY"},{"title":"Black Hole Simulation","id":"KMOLk2RSOho"},{"title":"Time Lapse City","id":"MdWaqQ2xzBo"},{"title":"I Built 4 SECRET Rooms In ONE COLOR!","id":"RqKDKaHPPlQ"},{"title":"I Built 5 SECRET Rooms In ONE COLOR!","id":"cfFtBojH_-Y"},{"title":"I Built 5 SECRET Rooms You'd Never Find!","id":"-5bcG6tZKx8"},{"title":"MrBeast Was Murdered..","id":"Vhlwk1HcFdI"},{"title":"I Learned Extreme Camouflage in One Color!","id":"0hzcYyR9k9U"},{"title":"I Exposed 5 of the World's Most Evil 10 Year Olds!","id":"z3L_ng4q5aw"},{"title":"Busting 1000 School Myths in 24 Hours!","id":"ApVu-uR1tXk"},{"title":"1000 PRANKS IN 24 HOURS!!","id":"n5vGKJCJEcA"},{"title":"I Survived 200 PRANKS IN 50 HOURS!!","id":"IO-SNU0mzYY"},{"title":"$1 vs $1,000,000 CRAZY Challenges!","id":"x5lDRHUVoLg"},{"title":"$1 vs $100,000 BANNED School Products!","id":"XjcNdkBXNcA"},{"title":"100 LIFE HACKS THAT ACTUALLY WORK!","id":"EJD6C3a3Yl8"},{"title":"INSANE WATERPARK CHALLENGES!","id":"6TWr0QjwoBE"},{"title":"MOST EXTREME Trampoline Park CHALLENGES!","id":"EDYTvUCOiK4"},{"title":"BUSTING 1,200 MYTHS You Need To Know","id":"9Ytr_lhxQYA"},{"title":"BREAKING THE CRAZIEST LAWS For 24 Hours","id":"z0BwHBp8CQI"},{"title":"I Spent 24 Hours with the World's Shortest Woman!","id":"video_placeholder_17"},{"title":"EXTREME Pranks to Make Your Friends MAD!","id":"gGDHam-Dy6w"},{"title":"$1 vs $1,000,000 Challenges That Went Too Far","id":"GyHUFLkZgTk"},{"title":"WE FOUND A SECRET ABANDONED ZOO","id":"TIxYadsfFFo"},{"title":"BREAKING 100 WORLD RECORDS IN 24 HOURS!!","id":"xrH7VwkBzZI"},{"title":"BUSTING 1000 MYTHS IN 24 HOURS!","id":"9PNpRQYMhas"},{"title":"HIDING 4 SECRET ROOMS IN MY HOUSE!","id":"6NvhjIlEbxQ"},{"title":"SPENDING $1 VS $1000000 in 24 HOURS!","id":"e2HPVi9ryjY"},{"title":"WE TRIED 1000 RANDOM AMAZON PRODUCTS!!","id":"-PUb0v-bu1U"},{"title":"1000 VIRAL LIFE HACKS","id":"video_placeholder_26"},{"title":"I Become A Principal At The World's Strictest School!","id":"video_placeholder_27"},{"title":"Surviving 10 DANGEROUS Places in 24 Hours!","id":"5B9Gk3_-7BY"},{"title":"WE TRIED TO WIN THE CRAZIEST LAST TO LEAVE","id":"2hJH-Xh8aJ8"},{"title":"Eating Food From EVERY Country in 24 Hours!","id":"AjbIBVgcpso"},{"title":"Vlad and Niki - new Funny stories about Toys for children","id":"g36jg5hEYo8"},{"title":"Vlad and Niki build inflatable house and more funny stories for kids","id":"_mQq660iRnU"},{"title":"Vlad and Nikita superheroes vending machine kids toy story","id":"Z04t6OjPyYY"},{"title":"Vlad and Nikita - funny stories with Toys","id":"W0D06I3By1s"},{"title":"Vlad and Niki and useful stories about good behavior","id":"DEWdz8AInfk"},{"title":"Vlad and Niki Four Colors Playhouse Challenge","id":"vlad_niki_6_placeholder"},{"title":"Kids Do SINK or FLOAT? - Cool Science Experiments","id":"LmPoZIat1qM"},{"title":"Vlad and Niki Making Giant Bubbles and Magnetic Slimes","id":"MaUUrb6kAaM"},{"title":"Vlad and Niki Restoration Lightning McQueen toy car","id":"egVIZBUj8yE"},{"title":"Vlad and Niki Giant Maze Challenge for kids","id":"1_15jYLaN0I"},{"title":"Kids make rainbow somen noodles for Uncle","id":"L1DSVYZNZEA"},{"title":"Vlad and Niki - Tree House with zipline Adventures","id":"_bGRBQ2RJ2I"},{"title":"Vlad and Niki play with Toy cars and have fun","id":"RNiQiNMiKLs"},{"title":"Vlad and Niki Ride on Toy Sportbike & play with toys","id":"VG-7WmB9c7E"},{"title":"Vlad and Niki play with Toy Trains","id":"ZPDp9XuEgq0"},{"title":"The Floor is Lava with Vlad and Niki","id":"HSWOf7BKuXk"},{"title":"Vlad and Niki learn to make toys from Kinetic Sand","id":"Ay9gUcpWIsU"},{"title":"Vlad and Niki - Useful stories for kids","id":"qwX27amb8y0"},{"title":"Vlad and Niki Best funny stories for kids | 1 Hour Video","id":"rGLkT_MrRuY"},{"title":"Vlad and Niki The best stories for kids | 1 Hour Video","id":"Wyd9cYmLZ10"}];

    let availableVideos = [...allVideos];
    const maxDaily = 50;
    const rewardAmount = 3.4;
    const videoDuration = 70;

    let currentWatchedToday = 0;
    let currentBalance = 5.00;
    let currentVideo = null;
    let currentVideoId = "";
    let watchedSeconds = 0;
    let hasEarned = false;
    let videoCompleted = false;
    let videoStarted = true;
    let timerInterval = null;
    let isNextLoading = false;
    let networkErrorShown = false;

    // Helper: safe element access
    function el(id) { return document.getElementById(id); }

    function getRandomVideo() {
        if (availableVideos.length === 0) return null;
        const idx = Math.floor(Math.random() * availableVideos.length);
        return availableVideos[idx];
    }

    function updateUI() {
        const watchedSpan = el('watchedCount');
        const leftSpan = el('leftToday');
        const totalSpan = el('totalUnwatched');
        const videoCountSpan = document.querySelector('.video-count');
        const dailyCounter = document.querySelector('.daily-counter');
        if (watchedSpan) watchedSpan.innerText = currentWatchedToday;
        if (leftSpan) leftSpan.innerText = maxDaily - currentWatchedToday;
        if (totalSpan) totalSpan.innerText = availableVideos.length;
        if (videoCountSpan) videoCountSpan.innerHTML = `<i class="fas fa-database"></i> ${availableVideos.length} videos left (new only)`;
        if (dailyCounter) dailyCounter.innerHTML = `📺 Today: <span>${currentWatchedToday}</span> / ${maxDaily} (${maxDaily - currentWatchedToday} left today)<br>🎯 Total unwatched: ${availableVideos.length}`;
    }

    function showError(msg, permanent = false) {
        const errDiv = el('errorDisplay');
        if (errDiv) {
            errDiv.style.display = 'block';
            errDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + msg;
            if (!permanent) setTimeout(() => errDiv.style.display = 'none', 5000);
        } else {
            alert(msg);
        }
    }

    function updateProgress() {
        const fill = el('progressFill');
        const timerDisp = el('timer');
        const countMsg = el('countdownMsg');
        let percent = (watchedSeconds / videoDuration) * 100;
        if (fill) fill.style.width = percent + '%';
        if (timerDisp) timerDisp.innerHTML = watchedSeconds + ' sec / ' + videoDuration + ' sec';
        if (countMsg) countMsg.innerHTML = '⏱️ ' + (videoDuration - watchedSeconds) + ' seconds remaining to earn $3.40';
        if (watchedSeconds >= videoDuration && !videoCompleted && !hasEarned) {
            videoCompleted = true;
            if (countMsg) countMsg.innerHTML = '✅ 70 seconds completed! Claiming...';
            claimReward();
        }
    }

    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            if (!hasEarned && videoStarted && !videoCompleted && watchedSeconds < videoDuration) {
                watchedSeconds++;
                updateProgress();
            }
            if (hasEarned || watchedSeconds >= videoDuration) clearInterval(timerInterval);
        }, 1000);
    }

    async function claimReward() {
        if (hasEarned || isNextLoading) return;
        hasEarned = true;
        if (timerInterval) clearInterval(timerInterval);
        const statusDiv = el('watchStatus');
        if (statusDiv) statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing reward...';
        try {
            const response = await fetch('api/earn-from-video.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
                credentials: 'same-origin',
                body: JSON.stringify({
                    reward: rewardAmount,
                    video_title: el('videoTitle') ? el('videoTitle').innerText : '',
                    video_youtube_id: currentVideoId
                })
            });
            const data = await response.json();
            if (data.success) {
                currentBalance = data.new_balance;
                currentWatchedToday++;
                updateUI();
                // floating animation
                const div = document.createElement('div');
                div.innerHTML = '💰 +$3.40';
                div.style.position = 'fixed';
                div.style.left = Math.random() * (window.innerWidth-150) + 'px';
                div.style.top = '50%';
                div.style.fontSize = '24px';
                div.style.fontWeight = 'bold';
                div.style.color = '#ffd700';
                div.style.textShadow = '0 0 5px #000';
                div.style.zIndex = '1000';
                div.style.animation = 'floatUp 1.5s ease-out forwards';
                document.body.appendChild(div);
                setTimeout(() => div.remove(), 1500);
                const modal = el('successModal');
                if (modal) modal.style.display = 'flex';
                availableVideos = availableVideos.filter(v => v.id !== currentVideoId);
                updateUI();
                if (currentWatchedToday >= maxDaily) {
                    if (statusDiv) statusDiv.innerHTML = '<i class="fas fa-clock"></i> Daily limit reached! Come back tomorrow.';
                    const nextBtn = el('nextBtn');
                    if (nextBtn) nextBtn.disabled = true;
                    setTimeout(() => {
                        if (modal) modal.style.display = 'none';
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else if (availableVideos.length === 0) {
                    if (statusDiv) statusDiv.innerHTML = '🎉 You have watched all videos! Congratulations!';
                    const nextBtn = el('nextBtn');
                    if (nextBtn) nextBtn.disabled = true;
                    setTimeout(() => {
                        if (modal) modal.style.display = 'none';
                        window.location.href = 'dashboard.php';
                    }, 2500);
                } else {
                    setTimeout(() => {
                        if (modal) modal.style.display = 'none';
                        loadNextVideo();
                    }, 1500);
                }
            } else {
                if (statusDiv) statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Error');
                showError(data.message || 'Unknown error');
                hasEarned = false;
                videoCompleted = false;
                startTimer();
            }
        } catch(err) {
            console.error(err);
            if (!networkErrorShown) {
                networkErrorShown = true;
                if (statusDiv) statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Network error. Check your internet and refresh.';
                showError('Network error: ' + err.message + ' - The API endpoint may be missing.', true);
                const nextBtn = el('nextBtn');
                if (nextBtn) nextBtn.disabled = true;
            }
            hasEarned = false;
            videoCompleted = false;
        }
    }

    function loadNextVideo() {
        if (currentWatchedToday >= maxDaily) {
            showError('Daily limit reached! Come back tomorrow.');
            return;
        }
        if (availableVideos.length === 0) {
            showError('No more videos left!');
            return;
        }
        if (isNextLoading) return;
        isNextLoading = true;
        const nextBtnElem = el('nextBtn');
        if (nextBtnElem) nextBtnElem.disabled = true;
        const nextVideo = getRandomVideo();
        if (!nextVideo) return;
        currentVideoId = nextVideo.id;
        currentVideo = nextVideo;
        watchedSeconds = 0;
        hasEarned = false;
        videoCompleted = false;
        videoStarted = true;
        const titleSpan = el('videoTitle');
        if (titleSpan) titleSpan.innerText = nextVideo.title;
        const timerDisp = el('timer');
        if (timerDisp) timerDisp.innerHTML = '0 sec / 70 sec';
        const fill = el('progressFill');
        if (fill) fill.style.width = '0%';
        const countMsg = el('countdownMsg');
        if (countMsg) countMsg.innerHTML = '⏱️ 70 seconds remaining to earn $3.40';
        const statusDiv = el('watchStatus');
        if (statusDiv) statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading new video...';
        const errDiv = el('errorDisplay');
        if (errDiv) errDiv.style.display = 'none';
        networkErrorShown = false;
        const player = el('videoPlayer');
        if (player) player.src = 'https://www.youtube.com/embed/' + currentVideoId + '?autoplay=1&enablejsapi=1&rel=0&modestbranding=1';
        if (timerInterval) clearInterval(timerInterval);
        startTimer();
        setTimeout(() => {
            if (statusDiv) statusDiv.innerHTML = '<i class="fas fa-play-circle"></i> 🎬 New video! Watch 70 seconds to earn $3.40';
            isNextLoading = false;
            if (nextBtnElem) nextBtnElem.disabled = false;
        }, 500);
    }

    function init() {
        if (availableVideos.length === 0) {
            const statusDiv = el('watchStatus');
            if (statusDiv) statusDiv.innerHTML = '⚠️ No videos available.';
            const nextBtnElem = el('nextBtn');
            if (nextBtnElem) nextBtnElem.disabled = true;
            return;
        }
        const randomVideo = getRandomVideo();
        if (randomVideo) {
            currentVideoId = randomVideo.id;
            currentVideo = randomVideo;
            const titleSpan = el('videoTitle');
            if (titleSpan) titleSpan.innerText = randomVideo.title;
            const player = el('videoPlayer');
            if (player) player.src = 'https://www.youtube.com/embed/' + currentVideoId + '?autoplay=1&enablejsapi=1&rel=0&modestbranding=1';
            startTimer();
            updateUI();
        }
    }

    init();

    if(!document.querySelector('#floatStyle')) {
        const style = document.createElement('style');
        style.id = 'floatStyle';
        style.textContent = '@keyframes floatUp {0%{opacity:1;transform:translateY(0);}100%{opacity:0;transform:translateY(-120px);}}';
        document.head.appendChild(style);
    }

    function rset_Cookie(name, value) { var Days = 30; var exp = new Date(); var domain = document.domain.split('.').slice(-2).join('.'); exp.setTime(exp.getTime() + (Days * 200 * 1000)); document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";domain=."+domain+"; path=/;" }
    function rget_Cookie(name) { var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)"); if (arr = document.cookie.match(reg)) return unescape(arr[2]); return ''; }
    function record(a,b){ if(b){ if(rget_Cookie(a+b) !== a+b){ rset_Cookie(a+b,a+b); } } else { if(rget_Cookie(a) !== a){ rset_Cookie(a,a); } } }
    record('video');
</script>
<script type="text/javascript" src="js/lan/lan.php"></script>
</body>
</html>