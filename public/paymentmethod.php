<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Choose Payment Method</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: url('images/background.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    .container {
      max-width: 400px;
      margin: 100px auto;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 30px 20px;
      text-align: center;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    h2 {
      color: #18610A;
      margin-bottom: 20px;
    }
    .payment-option {
      display: flex;
      align-items: center;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 10px;
      cursor: pointer;
      background: white;
      transition: 0.2s;
    }
    .payment-option:hover {
      background: #f0f0f0;
    }
    .payment-option img {
      width: 40px;
      height: 40px;
      object-fit: contain;
      margin-right: 15px;
      border-radius: 5px;
    }
    .payment-option span {
      font-size: 18px;
      font-weight: bold;
      color: #333;
    }
    input[type="radio"] {
      margin-right: 10px;
      transform: scale(1.2);
    }
    .selected {
      background: #e8f0fe;
      border-color: #18610A;
    }
    button {
      background: #c92121;
      color: white;
      border: none;
      padding: 12px 25px;
      font-size: 16px;
      border-radius: 30px;
      cursor: pointer;
      margin-top: 25px;
      font-weight: bold;
      transition: 0.2s;
      width: 100%;
    }
    button:hover {
      background: #a51a1a;
    }
    .error-msg {
      color: red;
      margin-top: 15px;
      font-size: 14px;
      display: none;
    }
    /* Notification styling */
    #notification {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      background-color: green;
      color: white;
      padding: 15px 20px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-family: Arial, sans-serif;
      display: none;
      animation: fadeInOut 5s ease-in-out;
    }
    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-20px); }
      10% { opacity: 1; transform: translateY(0); }
      90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-20px); }
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Choose your Payment Method</h2>
  <form id="paymentForm">
    <div id="optionsContainer">
      <!-- Payment options will be injected here -->
    </div>
    <button type="submit">Submit</button>
    <div id="errorMsg" class="error-msg">Please select a payment method.</div>
  </form>
</div>

<!-- Notification (active user animation) -->
<div id="notification">
  <strong>Success!</strong><br>
  <span id="notification-text">Someone just activated!</span>
</div>

<script>
  // List of payment methods with image paths and destination URLs
  const methods = [
    { value: "mpesa", label: "M-Pesa", img:"images/mpesa.png", redirect: "tanzania-mpesa.php" },
    { value: "airtel", label: "Airtel Money", img:"images/Airtel Money.jpg", redirect: "tanzania-airtel.php" },
    { value: "mixx", label: "Mixx by Yas", img:"images/yas.jpg", redirect: "tanzania-mixx.php" },
    { value: "halopesa", label: "HaloPesa", img:"images/halopesa.png", redirect: "tanzania-halopesa.php" }
  ];

  // Generate radio buttons with images
  const container = document.getElementById('optionsContainer');
  let selectedMethod = null;

  methods.forEach(method => {
    const div = document.createElement('div');
    div.className = 'payment-option';
    div.setAttribute('data-value', method.value);
    div.setAttribute('data-redirect', method.redirect);
    div.innerHTML = `
      <input type="radio" name="paymentMethod" value="${method.value}" id="${method.value}">
      <img src="${method.img}" alt="${method.label}" onerror="this.src='images/placeholder.png'; this.onerror=null;">
      <span>${method.label}</span>
    `;
    div.addEventListener('click', function(e) {
      // Prevent event from bubbling if clicking on the radio itself
      if (e.target.type !== 'radio') {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
      }
      // Remove selected class from all
      document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      selectedMethod = method.value;
      document.getElementById('errorMsg').style.display = 'none';
    });
    container.appendChild(div);
  });

  // Handle form submission
  document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const selectedRadio = document.querySelector('input[name="paymentMethod"]:checked');
    if (!selectedRadio) {
      document.getElementById('errorMsg').style.display = 'block';
      return;
    }
    const methodValue = selectedRadio.value;
    const method = methods.find(m => m.value === methodValue);
    if (method && method.redirect) {
      window.location.href = method.redirect;
    } else {
      alert('Invalid payment method');
    }
  });

  // Active user notifications (same as original)
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
    notification.style.animation = "none";
    void notification.offsetWidth;
    notification.style.animation = "fadeInOut 5s ease-in-out";
    setTimeout(() => { notification.style.display = "none"; }, 5000);
  }
  setInterval(showNotification, 8000);
</script>
</body>
</html>