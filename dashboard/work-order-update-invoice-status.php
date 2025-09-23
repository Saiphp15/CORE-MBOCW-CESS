<?php
// update-invoice-status.php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Include your database connection file
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoiceId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['status']) ? $_POST['status'] : '';
    $rejectionReason = isset($_POST['reason']) ? $_POST['reason'] : null;

    if ($invoiceId > 0 && ($action === 'verified' || $action === 'rejected')) {
        $newStatus = ($action === 'verified') ? 1 : 3;
        $paymentStatus = ($action === 'verified') ? 'Paid' : 'rejected';
        
        // Start a transaction to ensure both updates succeed or fail together
        $conn->begin_transaction();

        try {
            // Step 1: Update the bulk_projects_invoices_history table
            if ($action === 'verified') {
                $stmt_bulk = $conn->prepare("UPDATE cess_payment_history SET is_payment_verified = ? WHERE id = ?");
                $stmt_bulk->bind_param("ii", $newStatus, $invoiceId);
            } else { // 'rejected'
                $stmt_bulk = $conn->prepare("UPDATE cess_payment_history SET is_payment_verified = ?, rejection_reason = ? WHERE id = ?");
                $stmt_bulk->bind_param("isi", $newStatus, $rejectionReason, $invoiceId);
            }
            $stmt_bulk->execute();

            if ($stmt_bulk->affected_rows > 0) {
                // Step 2: Update the cess_payment_history table
                // $stmt_cess = $conn->prepare("UPDATE cess_payment_history SET is_payment_verified = ?, payment_status = ? WHERE bulk_invoice_id = ?");
                // $stmt_cess->bind_param("isi", $newStatus, $paymentStatus, $invoiceId);
                // $stmt_cess->execute();

                $conn->commit();
                
                // Return success message
                if ($action === 'rejected') {
                    echo json_encode(['status' => 'success', 'message' => 'Invoice status updated successfully.', 'reason' => $rejectionReason]);
                } else {
                    echo json_encode(['status' => 'success', 'message' => 'Invoice status updated successfully.']);
                }

            } else {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'No changes made. The invoice may not exist or its status is already updated.']);
            }
            
            $stmt_bulk->close();
            // $stmt_cess->close();

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid invoice ID or action.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
