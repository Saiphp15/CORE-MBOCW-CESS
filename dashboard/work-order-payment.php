<?php
// File: C:\wamp64\www\CORE-MBOCW-CESS\dashboard\razorpay-checkout.php
// This page displays payment details and initiates the Razorpay checkout popup.

session_start();

// Redirect if Razorpay session data is not set.
if (!isset($_SESSION['razorpay_checkout'])) {
    // You can redirect to a form or a different page to handle the missing data.
    header("Location: bulk-projects-invoice-cess-upload-form.php");
    exit();
}

// Get the checkout data from the session.
$checkoutData = $_SESSION['razorpay_checkout'];
unset($_SESSION['razorpay_checkout']); // Clear the session data after use for security.

// Include Razorpay SDK.
require_once('../vendor/autoload.php');
use Razorpay\Api\Api;

// Retrieve your Razorpay keys.
// NOTE: For production, these keys should be stored securely and not hardcoded.
$keyId = "rzp_test_K27QFBqZ8Wq02s";
$keySecret = "AU11vS10Yrn9mCYI2NuOLGgg";

// Initialize the Razorpay API with your keys.
$api = new Api($keyId, $keySecret);

// Retrieve user information from the session (customize as needed).
// These are used for prefilling the checkout form.
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$userMobile = isset($_SESSION['user_mobile']) ? $_SESSION['user_mobile'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Checkout</title>
    <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">
    <!-- Include Razorpay Checkout script from their official CDN. -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- Include Tailwind CSS for a modern and responsive design. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="container max-w-sm w-full">
        <div class="bg-white rounded-xl shadow-2xl p-8 text-center border border-gray-200">
            <div class="card-header border-b-2 pb-4 mb-4">
                <h4 class="text-2xl font-bold text-gray-800">Proceed to Payment</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <p class="text-gray-600 mb-2">Total amount to pay:</p>
                <p class="text-3xl font-extrabold text-green-600 mb-4">
                    INR <?php echo number_format($checkoutData['amount'] / 100, 2); ?>
                </p>
                
                <p class="text-gray-500 text-sm mb-6">
                    Order ID: <strong class="text-gray-700"><?php echo htmlspecialchars($checkoutData['order_id']); ?></strong>
                </p>

                <!-- The "Pay Now" button that triggers the Razorpay popup. -->
                <button id="rzp-button" class="w-full inline-block px-6 py-3 bg-green-500 text-white font-bold rounded-full transition duration-300 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Pay Now
                </button>
            </div>
        </div>
    </div>

    <script>
        // Options for the Razorpay Checkout popup.
        var options = {
            // Your Razorpay key.
            "key": "<?php echo htmlspecialchars($keyId); ?>",
            // The amount to pay in paise.
            "amount": "<?php echo htmlspecialchars($checkoutData['amount']); ?>",
            // Currency code.
            "currency": "<?php echo htmlspecialchars($checkoutData['currency']); ?>",
            // Your business name.
            "name": "<?php echo htmlspecialchars($checkoutData['name']); ?>",
            // A short description.
            "description": "<?php echo htmlspecialchars($checkoutData['description']); ?>",
            // URL of a logo.
            "image": "<?php echo htmlspecialchars($checkoutData['image']); ?>",
            // Razorpay Order ID.
            "order_id": "<?php echo htmlspecialchars($checkoutData['order_id']); ?>",
            // This function is called after the payment is successful.
            "handler": function (response){
                // Show a loading message while verification is in progress.
                var verificationMessage = document.createElement('div');
                verificationMessage.className = 'mt-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg';
                verificationMessage.textContent = 'Verifying payment... Please do not close this window.';
                document.querySelector('.card-body').appendChild(verificationMessage);

                // Send the payment response data to the server for verification.
                fetch('work-order-webhook.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Redirect to the success page with the payment ID.
                        window.location.href = 'work-order-payment-success.php?payment_id=' + data.payment_id;
                    } else {
                        // Redirect to the failure page with the error message.
                        window.location.href = 'work-order-payment-failed.php?error=' + encodeURIComponent(data.message || 'Payment verification failed.');
                    }
                })
                .catch(error => {
                    console.error('Error during verification:', error);
                    // Redirect to the failure page on a network or other error.
                    window.location.href = 'work-order-payment-failed.php?error=' + encodeURIComponent('An error occurred during verification.');
                });
            },
            "modal": {
                // Optional: Handle what happens when the user closes the popup.
                "ondismiss": function(){
                    console.log("Payment popup closed.");
                    // You might want to display a message or redirect the user.
                }
            },
            "prefill": {
                // Prefill the form with user information.
                "name": "<?php echo htmlspecialchars($userName); ?>",
                "email": "<?php echo htmlspecialchars($userEmail); ?>",
                "contact": "<?php echo htmlspecialchars($userMobile); ?>"
            },
            // Include additional notes if available.
            "notes": <?php echo json_encode($checkoutData['notes']); ?>
        };

        // Create a new Razorpay instance.
        var rzp = new Razorpay(options);

        // Add a click event listener to the "Pay Now" button to open the popup.
        document.getElementById('rzp-button').onclick = function(e){
            rzp.open();
            e.preventDefault();
        }
    </script>
</body>
</html>
