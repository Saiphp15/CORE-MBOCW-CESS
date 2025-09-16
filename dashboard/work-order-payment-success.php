<?php
// File: payment-success.php
// This page is displayed to the user after a successful payment is verified.

// Start the session to use session variables.
session_start();

// Get the payment_id from the URL query parameter.
// The payment_id is used to reference the successful transaction.
$paymentId = isset($_GET['payment_id']) ? htmlspecialchars($_GET['payment_id']) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">
    <!-- Include Tailwind CSS via CDN for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles can be added here if needed, but Tailwind classes are sufficient */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <!-- Payment Success Card -->
    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl p-8 text-center border border-green-200">
        <div class="flex flex-col items-center">
            <!-- Success Icon (using an SVG for a clean look) -->
            <svg class="w-20 h-20 text-green-500 mb-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            
            <h1 class="text-3xl font-extrabold text-green-700 mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-6">Your payment has been successfully processed.</p>
            
            <!-- Display the Payment ID -->
            <div class="bg-green-50 text-green-800 p-4 rounded-lg w-full">
                <p class="font-semibold">Transaction ID:</p>
                <p class="font-mono text-sm break-all"><?php echo $paymentId; ?></p>
            </div>

            <!-- Back to Dashboard/Home Button -->
            <a href="projects.php" class="mt-6 w-full inline-block px-6 py-3 bg-green-500 text-white font-bold rounded-full transition duration-300 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                Go to Bulk Invoice History
            </a>
        </div>
    </div>

</body>
</html>
