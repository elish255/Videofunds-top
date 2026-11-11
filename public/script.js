$(document).ready(function () {
  // Initialize Select2 with custom rendering
  $('#paymentMethod').select2({
    placeholder: 'Select Payment Method',
    templateResult: formatOption,
    templateSelection: formatOption
  });

  // Function to render option with image
  function formatOption(state) {
    if (!state.id) return state.text;
    const img = $(state.element).data('img');
    if (!img) return state.text;
    return $(
      `<span class="payment-option">
        <img src="${img}" alt="${state.text}" onerror="this.style.display='none'" /> 
        ${state.text}
      </span>`
    );
  }

  // Handle form submission
  $('#paymentForm').on('submit', function (e) {
    e.preventDefault();
    const method = $('#paymentMethod').val();
    const errorDiv = $('#errorMsg');

    if (!method) {
      errorDiv.show();
      return;
    } else {
      errorDiv.hide();
    }

    // Redirect based on selected method
    switch (method) {
      case 'mpesa':
        window.location.href = 'tanzania-mpesa.php';
        break;
      case 'airtel':
        window.location.href = 'tanzania-airtel.php';
        break;
      case 'mixx':
        window.location.href = 'tanzania-mixx.php';
        break;
      case 'halopesa':
        window.location.href = 'tanzania-halopesa.php';
        break;
      default:
        alert('Please select a valid payment method');
    }
  });
});

// Global function for sending email (used by other pages)
function sendEmail(paymentMethod) {
  const name = prompt("Enter your name:");
  const amount = prompt("Enter amount paid:");
  if (!name || !amount) return;
  const subject = encodeURIComponent("I have paid, please confirm");
  const body = encodeURIComponent(
    `Hello,\n\nI have made a payment using ${paymentMethod}.\n\nName: ${name}\nAmount: ${amount}\nMethod: ${paymentMethod}\n\nPlease confirm.\n\nThanks!`
  );
  const email = "officialvideofunds@gmail.com";
  const gmailAppLink = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${subject}&body=${body}`;
  const mailtoLink = `mailto:${email}?subject=${subject}&body=${body}`;
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
  window.location.href = isIOS ? mailtoLink : gmailAppLink;
}
// Updated frontend code - put this in your main HTML file

async function initiatePayment() {
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    const payBtn = document.getElementById('payBtn');
    const statusDiv = document.getElementById('statusMessage');
    
    if (!phoneNumber) {
        showStatus('Please enter your phone number', 'error');
        return;
    }
    
    if (!/^(07|06)[0-9]{8}$/.test(phoneNumber)) {
        showStatus('Please enter a valid Tanzanian Airtel number (0712345678)', 'error');
        return;
    }
    
    payBtn.disabled = true;
    payBtn.textContent = 'Processing...';
    showStatus('Initiating payment...', 'loading');
    
    try {
        const response = await fetch('payment-proxy.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'initiate_payment',
                phone_number: phoneNumber,
                amount: 62500
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('✅ Payment request sent! Check your phone for PIN prompt. Your transaction ID: ' + result.transaction_id, 'success');
            
            // Store transaction ID
            localStorage.setItem('lastTransactionId', result.transaction_id);
            
            // Start checking status
            startStatusCheck(result.transaction_id);
            
            document.getElementById('phoneNumber').value = '';
        } else {
            showStatus('❌ ' + (result.message || 'Payment failed'), 'error');
            payBtn.disabled = false;
            payBtn.textContent = 'Pay Now with Airtel Money';
        }
        
    } catch (error) {
        console.error('Error:', error);
        showStatus('❌ Connection error. Please check your internet.', 'error');
        payBtn.disabled = false;
        payBtn.textContent = 'Pay Now with Airtel Money';
    }
}

function startStatusCheck(transactionId) {
    let attempts = 0;
    const interval = setInterval(async () => {
        attempts++;
        
        try {
            const response = await fetch('payment-proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'check_status',
                    transaction_id: transactionId
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'completed') {
                clearInterval(interval);
                showStatus('🎉 Payment successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = "exchange fee/index exchange.html";
                }, 2000);
            } else if (attempts >= 30) {
                clearInterval(interval);
                showStatus('⏰ Timeout. Please verify manually below.', 'error');
                document.getElementById('payBtn').disabled = false;
                document.getElementById('payBtn').textContent = 'Pay Now with Airtel Money';
            }
        } catch (error) {
            console.log('Status check error:', error);
        }
    }, 3000);
}

async function verifyTransaction() {
    const transactionId = document.getElementById('transactionId').value.trim();
    const verifyResult = document.getElementById('verifyResult');
    
    if (!transactionId) {
        verifyResult.innerHTML = '<span style="color: red;">❌ Please enter transaction ID</span>';
        return;
    }
    
    verifyResult.innerHTML = '<span style="color: orange;">⏳ Checking...</span>';
    
    try {
        const response = await fetch(`verify-transaction.php?transaction_id=${encodeURIComponent(transactionId)}`);
        const result = await response.json();
        
        if (result.valid) {
            verifyResult.innerHTML = '<span style="color: green;">✅ Verified! Redirecting...</span>';
            setTimeout(() => {
                window.location.href = "exchange fee/index exchange.html";
            }, 2000);
        } else {
            verifyResult.innerHTML = '<span style="color: red;">❌ Invalid transaction ID. Please try again or contact support.</span>';
        }
    } catch (error) {
        verifyResult.innerHTML = '<span style="color: red;">❌ Error verifying. Please try again.</span>';
    }
}