<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page.
    header("Location: ../login.php");
    exit;
}

// Include the database configuration file to establish a connection.
require_once '../config/db.php';

// Get invoice id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: projects.php"); // redirect to list
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("
SELECT 
cph.id, 
cph.cess_payment_mode,
cph.is_payment_verified,
cph.rejection_reason,
rt.order_id ,
rt.payment_id,
rt.amount,
rt.status,
rt.created_at as payment_date 
FROM cess_payment_history cph
LEFT JOIN razorpay_transactions rt ON cph.id = rt.cess_payment_history_id 
WHERE cph.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
if (!$invoice) {
    $_SESSION['error'] = "Invoice not found!";
    header("Location: projects.php");
    exit;
}

$paymentMode = $invoice['cess_payment_mode'] == 1 ? 'Net Banking' : ($invoice['cess_payment_mode'] == 2 ? 'Challan' : 'Unknown');

// Include the TCPDF library
require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';
// require __DIR__ . '/../vendor/autoload.php';

// use TCPDF;

// Create a new TCPDF object
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetFont('freeserif', '', 14);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MBOCW CESS');
$pdf->SetTitle('Payment Receipt');
$pdf->SetSubject('Payment Receipt');

// Add a page
$pdf->AddPage();

// Get the HTML content from your provided code
$html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .receipt-container { width: 100%; border: 1px solid #ccc; padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 1.5em; margin: 0; color: #800000; }
        .header h2 { font-size: 1.2em; margin: 0; color: #333; }
        .section-title { background-color: #f2f2f2; padding: 10px; margin-top: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border: 1px solid #ddd; }
        .label { font-weight: bold; width: 40%; }
        .value { width: 60%; }
        .footer { margin-top: 20px; text-align: center; }
    </style>
    <div class="receipt-container">
        <div class="header">
            <div>
                <h1>महाराष्ट्र इमारत व इतर बांधकाम कामगार कल्याणकारी मंडळ</h1>
                <h2>Maharashtra Building And Other Construction Worker\'s Welfare Board</h2>
            </div>
        </div>
        <div class="section-title">Payment Receipt Issue Date</div>
        <table>
            <tr>
                <td class="label">Payment Receipt Issued Date & Time:</td>
                <td class="value">' . date("Y-m-d H:i:s") . '</td>
            </tr>
        </table>
        <div class="section-title">Bulk Invoice Details</div>
        <table>
            <tr>
                <td class="label">Amount:</td>
                <td class="value">' . (isset($invoice['amount']) && $invoice['amount'] !== '' ? htmlspecialchars($invoice['amount']) : '0.00') . '</td>
            </tr>
            <tr>
                <td class="label">Payment Mode:</td>
                <td class="value">' . (isset($paymentMode) && $paymentMode !== '' ? htmlspecialchars($paymentMode) : 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Challan No/ Net Banking:</td>
                <td class="value">XXXXXXX</td>
            </tr>
            <tr>
                <td class="label">Chq./Ref.No./UTR No/Payment ID:</td>
                <td class="value">' . (isset($invoice['payment_id']) && $invoice['payment_id'] !== '' ? htmlspecialchars($invoice['payment_id']) : 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Payment Date:</td>
                <td class="value">' . (isset($invoice['payment_date']) && $invoice['payment_date'] !== '' ? htmlspecialchars(date('Y-m-d', strtotime($invoice['payment_date']))) : 'N/A') . '</td>
            </tr>
        </table>
        <div class="section-title">Payment Details</div>
        <table>
            <tr>
                <td class="label">Payment Status:</td>
                <td class="value">' . (isset($invoice['status']) && $invoice['status'] !== '' ? htmlspecialchars($invoice['status']) : 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Receipt ID:</td>
                <td class="value">CID/TH/PU/PC660070/2024</td>
            </tr>
        </table>
        <div class="footer"></div>
    </div>';

// Output the HTML content
// $pdf->writeHTML($html, true, false, true, false, '');
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('payment_receipt.pdf', 'D');
