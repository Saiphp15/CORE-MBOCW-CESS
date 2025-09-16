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

    $workorder_id = intval($_POST['workorder_id']) ;
    $amount = trim($_POST['amount'] ?? '');
    $payment_type = trim($_POST['payment_type'] ?? '');
    // echo "<pre>";
    // var_dump($workorder_id,$amount, $payment_type);die;
    // Basic validation
    if (empty($workorder_id) || empty($amount) || empty($payment_type)) {
        $_SESSION['error'] = "Please fill in all required fields (Amount, Payment type).";
        header("Location: projects.php");
        exit;
    }

    try {
        $checkStmt = $conn->prepare("SELECT * FROM project_work_orders WHERE id = ?");
        $checkStmt->bind_param("i", $workorder_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        //  print_r($result->num_rows);die;
        if ($result->num_rows === 0) {
            $conn->rollback();
            $_SESSION['error'] = "Work order not found.";
            header("Location: projects.php");
            exit;
        }
        $checkStmt->close();
        $workOrderData = $result->fetch_assoc();

        $projectId = $workOrderData['project_id'];
        $cessAmount = $workOrderData['work_order_cess_amount'];
        $gstCessAmount = ($amount * 0.02); // 2% GST on CESS amount
        $administrativeCost = ($amount * 0.05); // 5% Administrative cost
        $effectiveCessAmount = $amount + $gstCessAmount + $administrativeCost;
        $employerId = $workOrderData['employer_id'];
        $cessPaymentMode = 1;
        $cessReceiptFile = NULL;
        $paymentStatus = "Pending";
        $isPaymentVerified = 2;
        $invoiceUploadType = 'single';
        $createdBy = $_SESSION['user_id'];

        $cessPaymentHistoryInsertStmt = $conn->prepare("INSERT INTO cess_payment_history (project_id, workorder_id, invoice_amount, cess_amount, gst_cess_amount, administrative_cost, effective_cess_amount, employer_id, cess_payment_mode, cess_receipt_file, payment_status, is_payment_verified, invoice_upload_type, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $cessPaymentHistoryInsertStmt->bind_param("iidddddiissisi",$projectId, $workorder_id, $amount, $cessAmount, $gstCessAmount, $administrativeCost, $effectiveCessAmount, $employerId, $cessPaymentMode, $cessReceiptFile, $paymentStatus, $isPaymentVerified, $invoiceUploadType, $createdBy);
        $cessPaymentHistoryInsertStmt->execute();
        $amountInPaisa = $amount * 100;
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
            $razorpayTransactionInsertStmt->bind_param("siidssi", $orderId, $_SESSION['user_id'], $bulk_invoice_id, $amount, $razorpayOrder['status'], $requestData, $createdBy);
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
            $_SESSION['success'] = "Successfully uploaded and saved {$successfulInserts} projects. Redirecting to payment page...";
            header("Location: work-order-payment.php");
            exit();
        // $insertPaymentStmt = $conn->prepare(
        //     "INSERT INTO work_order_payments (workorder_id, amount, payment_type, created_at) VALUES (?, ?, ?, NOW())"
        // );
        // $insertPaymentStmt->bind_param("ids", $workorder_id, $amount, $payment_type);

        // if (!$insertPaymentStmt->execute()) {
        //     throw new mysqli_sql_exception("Failed to insert payment record.");
        // }
        
        // $insertPaymentStmt->close();

        $conn->commit();
        $_SESSION['success'] .= " Payment is capture.";
        header("Location: projects.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage(), 'code' => $e->getLine()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
