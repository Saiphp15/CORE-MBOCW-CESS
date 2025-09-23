<?php
// File: C:\wamp64\www\CORE-MBOCW-CESS\dashboard\razorpay-webhook.php
// This script is called by the Razorpay Checkout handler to verify the payment.
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include your database connection file
require_once '../config/db.php';

// Include Razorpay SDK
require_once('../vendor/autoload.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// --- Razorpay Configuration (IMPORTANT: Replace with your actual keys) ---
$keyId = "rzp_test_K27QFBqZ8Wq02s";
$keySecret = "AU11vS10Yrn9mCYI2NuOLGgg";

// Set a content type for the response
header('Content-Type: application/json');

try {
    $api = new Api($keyId, $keySecret);

    // Get the request body
    $requestBody = @file_get_contents('php://input');
    $data = json_decode($requestBody, true);

    // Check if the required data is present.
    if (empty($data['razorpay_payment_id']) || empty($data['razorpay_order_id']) || empty($data['razorpay_signature'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Missing required payment data.']);
        exit();
    }

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Invalid JSON received.");
    }

    $razorpayPaymentId = $data['razorpay_payment_id'];
    $razorpayOrderId = $data['razorpay_order_id'];
    $razorpaySignature = $data['razorpay_signature'];

    // Verify the signature for security
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id' => $razorpayOrderId,
        'razorpay_payment_id' => $razorpayPaymentId,
        'razorpay_signature' => $razorpaySignature
    ]);

    // Start a transaction to update multiple tables atomically
    $conn->begin_transaction();

    try {
        // Prepare statements for updating tables
        $updateRazorpayStmt = $conn->prepare("UPDATE razorpay_transactions SET payment_id = ?, signature = ?, status = 'paid', response_data = ?, workorder_id = ? WHERE order_id = ?");
        // $updateBulkHistoryStmt = $conn->prepare("UPDATE bulk_projects_invoices_history SET is_payment_verified = 2 WHERE id = ?"); // 1 for Verified, 2 for pending intill admin verifies
        $updateCessHistoryStmt = $conn->prepare("UPDATE cess_payment_history SET payment_status = 'Paid', is_payment_verified = 2 WHERE workorder_id = ? AND payment_status = 'Pending'"); 

        // Get the bulk invoice ID from the razorpay_transactions table
        $getBulkInvoiceIdStmt = $conn->prepare("SELECT * FROM razorpay_transactions WHERE order_id = ?");
        $getBulkInvoiceIdStmt->bind_param("s", $razorpayOrderId);
        $getBulkInvoiceIdStmt->execute();
        $result = $getBulkInvoiceIdStmt->get_result();
        $transaction = $result->fetch_assoc();
        $workOrderId = $transaction['request_data'] ? json_decode($transaction['request_data'], true)['notes']['workorder_id'] : null;

        if (!$workOrderId) {
            throw new \Exception("Workorder ID not found for this order.");
        }
        
        // Update razorpay_transactions table
        $responseData = json_encode($data);
        $updateRazorpayStmt->bind_param("sssis", $razorpayPaymentId, $razorpaySignature, $responseData, $workOrderId, $razorpayOrderId);
        $updateRazorpayStmt->execute();


        $updateCessHistoryStmt->bind_param("i", $workOrderId);
        $updateCessHistoryStmt->execute();

        // Commit the transaction
        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Payment verified and database updated.', 'payment_id' => $razorpayPaymentId]);
    
    } catch (\Exception $e) {
        $conn->rollback();
        // Log the internal error
        error_log("Internal Server Error: " . $e->getMessage());
        print_r($e->getMessage());
        print_r($e->getLine());
        die;
        echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred during verification.']);
    }

} catch (SignatureVerificationError $e) {
    // Log the signature verification error
    error_log("Signature Verification Failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Signature verification failed.']);

} catch (\Exception $e) {
    // Log other errors
    error_log("Payment Verification Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed: ' . $e->getMessage()]);

} finally {
    // Close the database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>
