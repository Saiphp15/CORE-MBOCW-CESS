<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Include your database connection file
require_once '../config/db.php';
require_once('../vendor/autoload.php');
use Razorpay\Api\Api;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_id = intval($_POST['project_id']) ;
    $workorder_id = intval($_POST['workorder_id']) ;
    $invoice_amount = trim($_POST['amount'] ?? '');
    $payment_type = trim($_POST['payment_type'] ?? '');
    
    // Basic validation
    if (empty($workorder_id) || empty($invoice_amount) || empty($payment_type)) {
        $_SESSION['error'] = "Please fill in all required fields (Amount, Payment type).";
        header("Location: raise-workorder-invoice.php?workorder_id=$workorder_id&project_id=$project_id"); exit;
    }

    try {
        $conn->begin_transaction();

        $checkStmt = $conn->prepare("SELECT * FROM project_work_orders WHERE id = ?");
        $checkStmt->bind_param("i", $workorder_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        //  print_r($result->num_rows);die;
        if ($result->num_rows === 0) {
            $conn->rollback();
            $_SESSION['error'] = "Work order not found.";
            header("Location: raise-workorder-invoice.php?workorder_id=$workorder_id&project_id=$project_id"); exit;
        }
        $checkStmt->close();
        $workOrderData = $result->fetch_assoc();

        $cess_amount = ($invoice_amount * 0.01); // 1% GST on CESS amount
        $gst_cess_amount = $cess_amount; //2% GST on CESS amount
        $administrative_cost = ($gst_cess_amount * 0.01); // 5% Administrative cost
        $effective_cess_amount = $gst_cess_amount - $administrative_cost;
        $employer_id = $workOrderData['employer_id'];
        $cessPaymentMode = 1;
        $cessReceiptFile = NULL;
        $paymentStatus = "Pending";
        $isPaymentVerified = 2;
        $invoiceUploadType = 'single';
        $createdBy = $_SESSION['user_id'];

        $cessPaymentHistoryInsertStmt = $conn->prepare("INSERT INTO cess_payment_history (project_id, workorder_id, invoice_amount, cess_amount, gst_cess_amount, administrative_cost, effective_cess_amount, employer_id, cess_payment_mode, cess_receipt_file, payment_status, is_payment_verified, invoice_upload_type, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $cessPaymentHistoryInsertStmt->bind_param("iidddddiissisi",$project_id, $workorder_id, $invoice_amount, $cess_amount, $gst_cess_amount, $administrative_cost, $effective_cess_amount, $employer_id, $cessPaymentMode, $cessReceiptFile, $paymentStatus, $isPaymentVerified, $invoiceUploadType, $createdBy);
        $cessPaymentHistoryInsertStmt->execute();
        if ($cessPaymentHistoryInsertStmt->execute()) {
            $amountInPaisa = $invoice_amount * 100;
            $orderData = [
                'amount' => $amountInPaisa,
                'payment_capture' => 1,
                'currency' => 'INR',
                'receipt' => 'workorder_id' .  $workorder_id,
                'notes' => [
                    'workorder_id' => $workorder_id,
                    'user_id' => $_SESSION['user_id'],
                ]
            ];
            // --- Razorpay Configuration (IMPORTANT: Replace with your actual keys) ---
            $keyId = "rzp_test_K27QFBqZ8Wq02s"; // Replace with your key id
            $keySecret = "AU11vS10Yrn9mCYI2NuOLGgg"; // Replace with your key secret

            // Initialize Razorpay API
            $api = new Api($keyId, $keySecret);
            $razorpayOrder = $api->order->create($orderData);
            
            $razorpayTransactionInsertStmt = $conn->prepare("INSERT INTO razorpay_transactions (order_id, user_id, bulk_invoice_id, amount, status, request_data, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $orderId = $razorpayOrder['id'];
            $requestData = json_encode($orderData);
            $bulk_invoice_id = 0; 
            $razorpayTransactionInsertStmt->bind_param("siidssi", $orderId, $_SESSION['user_id'], $bulk_invoice_id, $invoice_amount, $razorpayOrder['status'], $requestData, $createdBy);
            $razorpayTransactionInsertStmt->execute();

            $conn->commit();

            $_SESSION['razorpay_checkout'] = [
                'order_id' => $orderId,
                'amount' => $amountInPaisa,
                'description' => 'Work order CESS Payment',
                'name' => 'MBOCW-CESS Portal',
                'image' => '../dist/img/MBOCWLogo.png', // Replace with your logo URL
                'currency' => 'INR',
                'notes' => $razorpayOrder['notes'],
            ];

            // Set the success message to be displayed after payment
            $_SESSION['success'] = "Invoice Raised Successfully.";
            header("Location: view-workorder-invoices.php?project_id=$project_id&workorder_id=$workorder_id"); exit();
        }else {
            $conn->rollback();
            $_SESSION['error'] = "Failed to save cess invoice history and razorpay transaction. Please try again.";
            header("Location: raise-workorder-invoice.php?workorder_id=$workorder_id&project_id=$project_id"); exit;
        }
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage(), 'code' => $e->getLine()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
