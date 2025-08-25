<?php
// At the top of your file, before the HTML
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
$stripe_publishable_key = $_ENV['STRIPE_PUBLISHABLE_KEY'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Payment Modal</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
        }

        /* Modal Styles */
        .payment-modal {
            display: none;
            position: fixed;
            z-index: 3000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: #ffffff;
            color: #222;
            padding: 2.5rem 2rem;
            border-radius: 20px;
            max-width: 90vw;
            width: 400px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: scale(0.9) translateY(-20px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #888;
            transition: color 0.2s ease;
        }

        .close-btn:hover {
            color: #333;
        }

        .modal-header {
            margin-bottom: 1.5rem;
        }

        .premium-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #a64aff, #6a00ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
        }

        .modal-title {
            color: #a64aff;
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .modal-description {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.5;
            font-size: 1rem;
        }

        .price-display {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .price-amount {
            font-size: 2.2rem;
            font-weight: 800;
            color: #a64aff;
            margin: 0;
        }

        .price-currency {
            font-size: 1rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .pay-btn, .back-btn {
            background: linear-gradient(135deg, #a64aff, #6a00ff);
            color: #fff;
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 35px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(166, 74, 255, 0.4);
            transition: all 0.3s ease;
            width: 100%;
            max-width: 200px;
        }

        .pay-btn:hover, .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(166, 74, 255, 0.5);
        }

        .pay-btn:active, .back-btn:active {
            transform: translateY(0);
        }

        .pay-btn:disabled, .back-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 1rem;
            display: none;
        }

        .success-message {
            color: #28a745;
            font-size: 0.9rem;
            margin-top: 1rem;
            display: none;
        }
    </style>
</head>
<body>

    <!-- Payment Modal -->
    <div id="paymentModal" class="payment-modal">
        <div class="modal-content">
            
            <div class="modal-header">
                <div class="premium-icon">👑</div>
                <h2 class="modal-title">Premium Feature</h2>
            </div>

            <p class="modal-description">
                The Chat Bot is available only for premium users.<br>
                <strong>Unlock advanced AI conversations and personalized responses!</strong>
            </p>

            <div class="price-display">
                <div class="price-amount">₹100</div>
                <div class="price-currency">One-time payment</div>
            </div>

            <button id="payNowBtn" class="pay-btn">
                <span id="payBtnText">Pay Now</span>
            </button>
            <button id="goBack" class="back-btn" onclick="window.location.href='user_home.php'">
                <span id="goBackText">Go Back</span>
            </button>

            <div id="errorMessage" class="error-message"></div>
            <div id="successMessage" class="success-message"></div>
        </div>
    </div>


<script>
const stripe = Stripe("<?= $stripe_publishable_key ?>"); // Replace with your Publishable Key

document.getElementById("payNowBtn").addEventListener("click", function () {
    fetch("components/create_checkout_session.php", {
        method: "POST",
    })
    .then(response => response.json())
    .then(session => {
        return stripe.redirectToCheckout({ sessionId: session.id });
    })
    .then(result => {
        if (result.error) {
            alert(result.error.message);
        }
    })
    .catch(error => console.error("Error:", error));
});
</script>


    <script>

        // Modal functions
        function showPaymentModal() {
            document.getElementById('paymentModal').style.display = 'flex';
        }


        function resetPaymentButton() {
            const payBtn = document.getElementById('payNowBtn');
            const payBtnText = document.getElementById('payBtnText');
            payBtn.disabled = false;
            payBtnText.innerHTML = 'Pay Now';
            hideMessages();
        }

        function showLoading() {
            const payBtn = document.getElementById('payNowBtn');
            const payBtnText = document.getElementById('payBtnText');
            payBtn.disabled = true;
            payBtnText.innerHTML = '<span class="loading"></span> Processing...';
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            document.getElementById('successMessage').style.display = 'none';
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            document.getElementById('errorMessage').style.display = 'none';
        }

        function hideMessages() {
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';
        }
    </script>
</body>
</html>