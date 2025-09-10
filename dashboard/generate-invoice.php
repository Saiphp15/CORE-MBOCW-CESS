<?php
// Include the TCPDF library
require_once '../vendor/tecnickcom/tcpdf/tcpdf.php'; 
// require __DIR__ . '/../vendor/autoload.php';

// use TCPDF;

// Create a new TCPDF object
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
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
        <div class="section-title">Project Details</div>
        <table>
            <tr>
                <td class="label">Project Name:</td>
                <td class="value">Test</td>
            </tr>
            <tr>
                <td class="label">Project ID:</td>
                <td class="value">CIDURBPWD1797758</td>
            </tr>
            <tr>
                <td class="label">Implementing Agency Name:</td>
                <td class="value">Demo</td>
            </tr>
            <tr>
                <td class="label">Department Name:</td>
                <td class="value">Housing</td>
            </tr>
            <tr>
                <td class="label">District:</td>
                <td class="value">Thane</td>
            </tr>
            <tr>
                <td class="label">Total Cess Paid:</td>
                <td class="value">XXX</td>
            </tr>
            <tr>
                <td class="label">Challan No/ Net Banking:</td>
                <td class="value">XXXXXXX</td>
            </tr>
            <tr>
                <td class="label">Chq./Ref.No./UTR No/Payment ID:</td>
                <td class="value">XXXXXXXX</td>
            </tr>
            <tr>
                <td class="label">Mode of Payment:</td>
                <td class="value">Challan / Net Banking</td>
            </tr>
            <tr>
                <td class="label">Payment Date:</td>
                <td class="value">Track Date Time of the Payment Made</td>
            </tr>
        </table>
        <div class="section-title">Payment Details</div>
        <table>
            <tr>
                <td class="label">Payment Status:</td>
                <td class="value">Complete/Success</td>
            </tr>
            <tr>
                <td class="label">Receipt ID:</td>
                <td class="value">CID/TH/PU/PC660070/2024</td>
            </tr>
        </table>
        <div class="footer">
            <p>Scan QR code to verify details of Payment Receipt</p>
        </div>
    </div>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('payment_receipt.pdf', 'D');