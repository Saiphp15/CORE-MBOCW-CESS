<?php
// File: payment-failed.php
// This page is displayed to the user if a payment fails or verification fails.
// Start the session to use session variables.
session_start();

// Get the error message from the URL query parameter.
// The error message provides details on why the payment failed.
$errorMessage = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : 'An unknown error occurred.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">
    <!-- Include Tailwind CSS via CDN for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles can be added here if needed */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <!-- Payment Failed Card -->
    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl p-8 text-center border border-red-200">
        <div class="flex flex-col items-center">
            <!-- Failure Icon (using an SVG for a clean look) -->
            <svg class="w-20 h-20 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <h1 class="text-3xl font-extrabold text-red-700 mb-2">Payment Failed</h1>
            <p class="text-gray-600 mb-6">Unfortunately, your payment could not be processed.</p>

            <!-- Display the error message -->
            <div class="bg-red-50 text-red-800 p-4 rounded-lg w-full">
                <p class="font-semibold">Error Details:</p>
                <p class="font-mono text-sm break-all"><?php echo $errorMessage; ?></p>
            </div>

            <!-- Retry Payment Button -->
            <a href="projects.php" class="mt-6 w-full inline-block px-6 py-3 bg-red-500 text-white font-bold rounded-full transition duration-300 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                Try Again
            </a>
        </div>
    </div>

</body>
</html>
