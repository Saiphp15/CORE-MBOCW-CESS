<?php
// C:\wamp64\www\CORE-MBOCW-CESS\dashboard\save-bulk-projects-invoces-cess.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include your database connection file
require_once '../config/db.php';

// Include the Composer autoloader for PhpSpreadsheet
require_once('../vendor/autoload.php'); // Adjust path as needed

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use Razorpay\Api\Api;

// --- Razorpay Configuration (IMPORTANT: Replace with your actual keys) ---
$keyId = "rzp_test_K27QFBqZ8Wq02s"; // Replace with your key id
$keySecret = "AU11vS10Yrn9mCYI2NuOLGgg"; // Replace with your key secret

// Initialize Razorpay API
$api = new Api($keyId, $keySecret);

// Initialize statement variables to null to ensure they are always in scope for closing
$employerCheckStmt = $employerInsertStmt = null;
$projectCategoryCheckStmt = $projectTypeCheckStmt = null;
$projectCheckStmt = $projectInsertStmt = null;
$workOrderCheckStmt = $workOrderInsertStmt = null;
$bulkProjectsInvoicesHistoryInsertStmt = null;
$cessPaymentHistoryInsertStmt = null;
$totalInvoicedWorkOrderStmt = $updateWorkOrderStatusStmt = null;
$totalInvoicedProjectStmt = $updateProjectStatusStmt = null;
$razorpayTransactionInsertStmt = null;

// Define the upload directory. Make sure this directory exists and is writable by the web server!
$uploadDir = '../uploads/bulk_upload_templates/';

// Check if the form was submitted and a file was uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['bulk_projects_invoices_cess'])) {
    
    $file = $_FILES['bulk_projects_invoices_cess'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];

    if ($fileError !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "File upload failed with error code: " . $fileError;
        header("Location: bulk-projects-invoice-cess-upload-form.php");
        exit();
    }

    // Create a secure and unique filename to prevent conflicts and security issues
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid('bulk_upload_') . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFileName;
    
    // Attempt to move the uploaded file to its permanent location
    if (!move_uploaded_file($fileTmpName, $uploadPath)) {
        $_SESSION['error'] = "Failed to move the uploaded file.";
        header("Location: bulk-projects-invoice-cess-upload-form.php");
        exit();
    }

    // Start a database transaction for data integrity
    $conn->begin_transaction();

    try {
        // Load the spreadsheet file
        // $spreadsheet = IOFactory::load($fileTmpName);
        // $worksheet = $spreadsheet->getActiveSheet();
        // Load the spreadsheet file from its new, permanent location
        $spreadsheet = IOFactory::load($uploadPath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Get the highest row and column to iterate through
        $highestRow = $worksheet->getHighestRow();
        
        // Prepare SQL statements once, outside the loop, for efficiency
        // 1. Check for existing employer
        $employerCheckStmt = $conn->prepare("SELECT id FROM employers WHERE email = ?");
        // 2. Insert new employer
        $employerInsertStmt = $conn->prepare("INSERT INTO employers (name, pancard, created_at, created_by) VALUES (?, ?, NOW(), ?)");
        
        // Check for existing project
        $projectCheckStmt = $conn->prepare("SELECT id, construction_cost FROM projects WHERE project_name = ?");
        $projectInsertStmt = $conn->prepare("INSERT INTO projects (project_code, project_name, local_authority_id, construction_cost, project_start_date, project_end_date, cess_amount, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
        // 5. Insert new work order
        // Check for existing project_work_orders record
        $workOrderCheckStmt = $conn->prepare("SELECT id, work_order_amount FROM project_work_orders WHERE project_id = ? AND work_order_number = ?");
        $workOrderInsertStmt = $conn->prepare("INSERT INTO project_work_orders (project_id, work_order_number, work_order_date, work_order_amount, work_order_cess_amount, work_order_gst_cess_amount, work_order_administrative_cost, work_order_effective_cess_amount, employer_id, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
        
        // 6. Bulk invoices history Insertion
        $bulkProjectsInvoicesHistoryInsertStmt = $conn->prepare("INSERT INTO bulk_projects_invoices_history (effective_cess_amount, bulk_project_invoices_template_file, cess_payment_mode, is_payment_verified, created_by) VALUES (?, ?, ?, ?, ?)");

        // 7. Insert invoice cess payment history (This is the single, consolidated table as discussed)
        $cessPaymentHistoryInsertStmt = $conn->prepare("INSERT INTO cess_payment_history (bulk_invoice_id, project_id, workorder_id, invoice_amount, cess_amount, gst_cess_amount, administrative_cost, effective_cess_amount, employer_id, cess_payment_mode, cess_receipt_file, payment_status, is_payment_verified, invoice_upload_type, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // 8. Statements to check invoice totals and update status
        $totalInvoicedWorkOrderStmt = $conn->prepare("SELECT SUM(invoice_amount) AS total_invoiced FROM cess_payment_history WHERE workorder_id = ?");
        $updateWorkOrderStatusStmt = $conn->prepare("UPDATE project_work_orders SET status = 'Completed' WHERE id = ?");

        $totalInvoicedProjectStmt = $conn->prepare("SELECT SUM(invoice_amount) AS total_invoiced FROM cess_payment_history WHERE project_id = ?");
        $updateProjectStatusStmt = $conn->prepare("UPDATE projects SET status = 'Completed' WHERE id = ?");

        // Prepare statement for the new Razorpay transactions table
        $razorpayTransactionInsertStmt = $conn->prepare("INSERT INTO razorpay_transactions (order_id, user_id, bulk_invoice_id, amount, status, request_data, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // --- Bulk Project Invoices History Insertion ---
        // These values are from the form, not the Excel rows
        $templateTotalEffectiveCessAmount = isset($_POST['effective_cess_amount']) ? $_POST['effective_cess_amount'] : '';
        $bulkProjectsInvoicesTemplateFile = $newFileName;
        $cessPaymentMode = 1; // Assuming 'Online' is mode 1
        $isPaymentVerified = 2; // Assuming 'Pending Verification from admin' is mode 2
        $createdBy = $_SESSION['user_id']; // Get the ID of the logged-in user

        $bulkProjectsInvoicesHistoryInsertStmt->bind_param("dsiii", $templateTotalEffectiveCessAmount, $bulkProjectsInvoicesTemplateFile, $cessPaymentMode, $isPaymentVerified, $createdBy);
        $bulkProjectsInvoicesHistoryInsertStmt->execute();
        $bulkInvoiceId = $bulkProjectsInvoicesHistoryInsertStmt->insert_id;

        // Initialize variables for tracking progress
        $rowCount = 0;
        $successfulInserts = 0;
        $errors = [];

        //get local authority id from local_authorities_users based on logged in user id
        // $localAuthorityId = null;
        // $getLoggedInUserLocalAuthorityId = $conn->query("SELECT local_authority_id FROM local_authorities_users WHERE user_id = " . $createdBy . " LIMIT 1");
        // if ($getLoggedInUserLocalAuthorityId->num_rows > 0) {
        //     $row = $getLoggedInUserLocalAuthorityId->fetch_assoc();
        //     $localAuthorityId = $row['local_authority_id'];
        // }

        // --- Get local_authority_id based on role ---

        //print_r($_SESSION); die;
        $localAuthorityId = null;

        // Find the role of the logged-in user
        // $userRoleStmt = $conn->prepare("SELECT role, created_by FROM users WHERE id = ?");
        // $userRoleStmt->bind_param("i", $createdBy);
        // $userRoleStmt->execute();
        // $userRoleResult = $userRoleStmt->get_result();
        // $userData = $userRoleResult->fetch_assoc();
        // $userRoleStmt->close();
        
       // if ($userData) {
     //       $role = strtolower($userData['role']); // e.g., "cfo", "engineer"
           //  echo '==='.$role; die; 
           $role = $_SESSION['user_role']; ;
            if ($role === 3) {
                // CFO → get local_authority_id from local_authorities_users 
                $getLocalAuthority = $conn->prepare("
                    SELECT local_authority_id 
                    FROM local_authorities_users 
                    WHERE user_id = ? AND is_active = 1 
                    LIMIT 1
                ");
                  
                $getLocalAuthority->bind_param("i", $createdBy);
                $getLocalAuthority->execute();
                $result = $getLocalAuthority->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $localAuthorityId = $row['local_authority_id'];
                }
                $getLocalAuthority->close();

            } elseif ($role === 7) {
                // Engineer → get creator’s local_authority_id   
                                
                    $getCreatorAuthority = $conn->prepare("
                        SELECT lau.local_authority_id , u.created_by
                        FROM local_authorities_users as lau 
                        JOIN users as u ON u.created_by = lau.user_id
                        WHERE u.id = u.created_by AND lau.user_id = ? AND lau.is_active = 1 
                        LIMIT 1
                    ");
                    $getCreatorAuthority->bind_param("i", $createdBy);
                    $getCreatorAuthority->execute();
                    $creatorAuthResult = $getCreatorAuthority->get_result();
                    if ($creatorAuthResult->num_rows > 0) {
                        $creatorAuthRow = $creatorAuthResult->fetch_assoc();
                        $localAuthorityId = $creatorAuthRow['local_authority_id'];
                    }
                    $getCreatorAuthority->close();
               
            }
      //  }

        // Final check — prevent inserting 0 if nothing found
        // if (!$localAuthorityId) {
        //     $_SESSION['error'] = "Unable to determine local authority for this user.";
        //     header("Location: bulk-projects-invoice-cess-upload-form.php");
        //     exit();
        // }


        // Loop through each row of the worksheet, starting from the second row (skipping the header)
        for ($row = 2; $row <= $highestRow; ++$row) {
            $rowCount++;

            try {
                // Get cell values from the row, ensuring type safety and handling nulls
                // It's good practice to use getCalculatedValue() for formula support
                $projectName = trim($worksheet->getCell('B' . $row)->getCalculatedValue() ?? '');
                $constructionCost = floatval($worksheet->getCell('C' . $row)->getCalculatedValue() ?? 0.0);

                // Handle date conversion carefully
                $projectStartDateValue = $worksheet->getCell('D' . $row)->getCalculatedValue();
                $projectStartDate = null;
                if (!empty($projectStartDateValue)) {
                    $projectStartDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($projectStartDateValue)->format('Y-m-d');
                }

                $projectEndDateValue = $worksheet->getCell('E' . $row)->getCalculatedValue();
                $projectEndDate = null;
                if (!empty($projectEndDateValue)) {
                    $projectEndDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($projectEndDateValue)->format('Y-m-d');
                }

                $workOrderNumber = trim($worksheet->getCell('F' . $row)->getCalculatedValue() ?? '');
                $workOrderDateValue = $worksheet->getCell('G' . $row)->getCalculatedValue();
                $workOrderDate = null;
                if (!empty($workOrderDateValue)) {
                    $workOrderDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($workOrderDateValue)->format('Y-m-d');
                }
                $workOrderAmount = floatval($worksheet->getCell('H' . $row)->getCalculatedValue() ?? 0.0);
                
                $invoiceAmount = floatval($worksheet->getCell('I' . $row)->getCalculatedValue() ?? 0.0);
                $cessAmount = floatval($worksheet->getCell('J' . $row)->getCalculatedValue() ?? 0.0);
                $administrativeCost = floatval($worksheet->getCell('K' . $row)->getCalculatedValue() ?? 0.0);
                $effectiveCessAmount = floatval($worksheet->getCell('L' . $row)->getCalculatedValue() ?? 0.0);

                $employerName = trim($worksheet->getCell('M' . $row)->getCalculatedValue() ?? '');
                $employerPancard = trim($worksheet->getCell('N' . $row)->getCalculatedValue() ?? '');
                
                $cessPaymentMode = 1; // hardcoded as per business logic for bulk uploads
                
                
                // --- 1. Employer Insertion/Lookup ---
                $employerId = null;

                // --- VALIDATION CHECKS BEFORE PROCESSING ---
                // Skip the row if required data is missing.
                if (empty($employerName) || empty($employerPancard)) {
                    $errors[] = "Row {$rowCount} skipped: Missing required employer information (Name, Pancard).";
                    continue; // Skip to the next row
                }

                $employerCheckStmt->bind_param("s", $employerEmail);
                $employerCheckStmt->execute();
                $result = $employerCheckStmt->get_result();
                if ($result->num_rows > 0) {
                    $employer = $result->fetch_assoc();
                    $employerId = $employer['id'];
                } else {
                    $employerInsertStmt->bind_param("ssi", $employerName, $employerPancard, $createdBy);
                    $employerInsertStmt->execute();
                    $employerId = $employerInsertStmt->insert_id;
                }

                // --- 4. Project Lookup/Insertion ---
                $projectId = null;
                $project_code = 'PC' . str_pad($rowCount, 5, '0', STR_PAD_LEFT); // Example project code generation
                $projectConstructionCost = 0.0;

                $projectCheckStmt->bind_param("s", $projectName);
                $projectCheckStmt->execute();
                $result = $projectCheckStmt->get_result();
                if ($result->num_rows > 0) {
                    // Project exists, get its ID and construction cost
                    $project = $result->fetch_assoc();
                    $projectId = $project['id'];
                    $projectConstructionCost = $project['construction_cost'];
                } else {
                    // Project does not exist, insert a new record
                    $projectInsertStmt->bind_param("ssidssdi", $project_code, $projectName, $localAuthorityId, $constructionCost, $projectStartDate, $projectEndDate, $cessAmount, $createdBy);
                    $projectInsertStmt->execute();
                    $projectId = $projectInsertStmt->insert_id;
                    $projectConstructionCost = $constructionCost;
                }
                
                // --- 5. Work Order Insertion/Lookup & VALIDATION ---
                $workOrderId = null;
                $workOrderTotalAmount = 0.0;

                $workOrderCheckStmt->bind_param("is", $projectId, $workOrderNumber);
                $workOrderCheckStmt->execute();
                $result = $workOrderCheckStmt->get_result();
                if ($result->num_rows > 0) {
                    // Work order exists, retrieve its ID and total amount
                    $workOrder = $result->fetch_assoc();
                    $workOrderId = $workOrder['id'];
                    $workOrderTotalAmount = $workOrder['work_order_amount'];
                    $errors[] = "Row {$rowCount} - Note: A work order with number '{$workOrderNumber}' for project '{$projectName}' already exists. Inserting invoice data to existing record.";
                } else {
                    // Work order does not exist, insert it
                    // NOTE: The calculation of cess amounts here seems to be a hardcoded business rule.
                    // If these percentages change, you'll need to update this code.
                    $workOrderCessAmount = $workOrderAmount * 0.01;
                    $workOrderGstCessAmount = $workOrderCessAmount; // This seems to be the Cess amount + GST on Cess (2.5%)
                    $workOrderAdministrativeCost = $workOrderGstCessAmount * 0.01;
                    $workOrderEffectiveCessAmount = $workOrderGstCessAmount - $workOrderAdministrativeCost;
                    
                    $workOrderInsertStmt->bind_param("issdddddii", $projectId, $workOrderNumber, $workOrderDate, $workOrderAmount, $workOrderCessAmount, $workOrderGstCessAmount, $workOrderAdministrativeCost, $workOrderEffectiveCessAmount, $employerId, $createdBy);
                    $workOrderInsertStmt->execute();
                    $workOrderId = $workOrderInsertStmt->insert_id;
                    $workOrderTotalAmount = $workOrderAmount; // Since it's new, the total is the current amount
                }
                
                // --- 6. Invoice Payment History Insertion ---
                $isPaymentVerified = 2; // default 2 until admin verifies payment received.
                $paymentStatus = 'Pending';
                
                // Get total previously invoiced amount for this work order from the consolidated table
                $totalInvoicedWorkOrderStmt->bind_param("i", $workOrderId);
                $totalInvoicedWorkOrderStmt->execute();
                $invoicedResult = $totalInvoicedWorkOrderStmt->get_result();
                $invoicedData = $invoicedResult->fetch_assoc();
                $totalPreviouslyInvoiced = $invoicedData['total_invoiced'] ?? 0;
                
                $remainingWorkOrderAmount = $workOrderTotalAmount - $totalPreviouslyInvoiced;

                $invoiceUploadType = 'bulk'; // enum('bulk', 'single')	

                // Check if the new invoice amount is greater than the remaining work order amount
                if ($invoiceAmount > $remainingWorkOrderAmount) {
                    $errors[] = "Row {$rowCount} skipped: Invoice amount ({$invoiceAmount}) exceeds the remaining work order amount ({$remainingWorkOrderAmount}) for work order '{$workOrderNumber}'.";
                } else {
                    // Invoice Cess Payment History Insertion ---
                    // This correctly inserts into the single `cess_payment_history` table as discussed previously.
                    $cessPaymentHistoryInsertStmt->bind_param("iiidddddiissisi", $bulkInvoiceId, $projectId, $workOrderId, $invoiceAmount, $cessAmount, $gstCessAmount, $administrativeCost, $effectiveCessAmount, $employerId, $cessPaymentMode, $cessReceiptFile, $paymentStatus, $isPaymentVerified, $invoiceUploadType, $createdBy);
                    $cessPaymentHistoryInsertStmt->execute();

                    // Add the effective cess amount to our running total ---
                    $totalValidatedCessAmount += $effectiveCessAmount;
                }
                
                // --- 7. Check and update work order status ---
                // Get the newly updated total for the work order
                $totalInvoicedWorkOrderStmt->bind_param("i", $workOrderId);
                $totalInvoicedWorkOrderStmt->execute();
                $invoicedResult = $totalInvoicedWorkOrderStmt->get_result();
                $invoicedData = $invoicedResult->fetch_assoc();
                $newTotalInvoicedWorkOrder = $invoicedData['total_invoiced'] ?? 0;
                
                // Compare with the work order's total amount
                // Using float comparison with a small epsilon can be safer than direct comparison
                if (abs($newTotalInvoicedWorkOrder - $workOrderTotalAmount) < 0.01 || $newTotalInvoicedWorkOrder > $workOrderTotalAmount) {
                    $updateWorkOrderStatusStmt->bind_param("i", $workOrderId);
                    $updateWorkOrderStatusStmt->execute();
                }

                // --- 8. Check and update project status ---
                $totalInvoicedProjectStmt->bind_param("i", $projectId);
                $totalInvoicedProjectStmt->execute();
                $projectInvoicedResult = $totalInvoicedProjectStmt->get_result();
                $projectInvoicedData = $projectInvoicedResult->fetch_assoc();
                $totalInvoicedForProject = $projectInvoicedData['total_invoiced'] ?? 0;

                // Again, using a float comparison for safety
                if (abs($totalInvoicedForProject - $projectConstructionCost) < 0.01 || $totalInvoicedForProject > $projectConstructionCost) {
                    $updateProjectStatusStmt->bind_param("i", $projectId);
                    $updateProjectStatusStmt->execute();
                }

                $successfulInserts++;
                
            } catch (CalculationException $e) {
                $errors[] = "Row {$rowCount} skipped due to a formula error: " . $e->getMessage();
            } catch (\Exception $e) {
                // Catch any other general exceptions during the loop
                $errors[] = "Error processing row {$rowCount}: " . $e->getMessage();
            }

        }

        // Final check on total processed rows
        if ($successfulInserts > 0) {
            // Update the history record with the correct total amount ---
            $bulkProjectsInvoicesHistoryUpdateStmt = $conn->prepare("UPDATE bulk_projects_invoices_history SET effective_cess_amount = ? WHERE id = ?");
            $bulkProjectsInvoicesHistoryUpdateStmt->bind_param("di", $totalValidatedCessAmount, $bulkInvoiceId);
            $bulkProjectsInvoicesHistoryUpdateStmt->execute();
            $bulkProjectsInvoicesHistoryUpdateStmt->close();

            // If data was successfully processed, proceed with Razorpay order creation
            $amountInPaisa = round($totalValidatedCessAmount * 100);
            
            $orderData = [
                'amount' => $amountInPaisa,
                'currency' => 'INR',
                'receipt' => 'bulk_invoice_' . $bulkInvoiceId,
                'notes' => [
                    'bulk_invoice_id' => $bulkInvoiceId,
                    'user_id' => $_SESSION['user_id'],
                ]
            ];
            
            $razorpayOrder = $api->order->create($orderData);
            
            // Log the order creation in the new table
            $orderId = $razorpayOrder['id'];
            $requestData = json_encode($orderData);
            $razorpayTransactionInsertStmt->bind_param("siidssi", $orderId, $_SESSION['user_id'], $bulkInvoiceId, $totalValidatedCessAmount, $razorpayOrder['status'], $requestData, $createdBy);
            $razorpayTransactionInsertStmt->execute();

            // Commit the database transaction
            $conn->commit();
            
            // Set success message and redirect to the Razorpay checkout page
            $_SESSION['razorpay_checkout'] = [
                'order_id' => $orderId,
                'amount' => $amountInPaisa,
                'description' => 'Cess Payment for Bulk Invoice Upload',
                'name' => 'MBOCW-CESS Portal',
                'image' => '../dist/img/MBOCWLogo.png', // Replace with your logo URL
                'currency' => 'INR',
                'notes' => $razorpayOrder['notes'],
            ];

            // Set the success message to be displayed after payment
            $_SESSION['success'] = "Successfully uploaded and saved {$successfulInserts} projects. Redirecting to payment page...";
            header("Location: razorpay-checkout.php");
            exit();

        } else {
            // If no data was processed, rollback and show an error
            $conn->rollback();
            $message = "No valid data found in the uploaded file.";
            if (!empty($errors)) {
                $message .= " Details: " . implode(" ", $errors);
            }
            $_SESSION['error'] = $message;
            header("Location: bulk-projects-invoice-cess-upload-form.php");
            exit();
        }

        // // If all good, commit the transaction
        // $conn->commit();

        // // Set a success message
        // $message = "Successfully uploaded and saved {$successfulInserts} projects.";
        // if (!empty($errors)) {
        //     $message .= " <br>Some rows were skipped due to errors: " . implode("<br>", $errors);
        //     $_SESSION['error'] = $message;
        // } else {
        //     $_SESSION['success'] = $message;
        // }

    } catch (ReaderException $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error reading the Excel file: " . $e->getMessage();
    } catch (\Exception $e) {
        // Handle any exceptions outside the loop
        $conn->rollback();
        $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    }
} else {
    // If the form wasn't submitted correctly
    $_SESSION['error'] = "Invalid request.";
}

// A more robust way to close all prepared statements and the connection
// This ensures they are closed even if an exception occurs
if ($employerCheckStmt) $employerCheckStmt->close();
if ($employerInsertStmt) $employerInsertStmt->close();
if ($projectCheckStmt) $projectCheckStmt->close();
if ($projectInsertStmt) $projectInsertStmt->close();
if ($workOrderCheckStmt) $workOrderCheckStmt->close();
if ($workOrderInsertStmt) $workOrderInsertStmt->close();
if ($bulkProjectsInvoicesHistoryInsertStmt) $bulkProjectsInvoicesHistoryInsertStmt->close();
if ($cessPaymentHistoryInsertStmt) $cessPaymentHistoryInsertStmt->close();
if ($totalInvoicedWorkOrderStmt) $totalInvoicedWorkOrderStmt->close();
if ($updateWorkOrderStatusStmt) $updateWorkOrderStatusStmt->close();
if ($totalInvoicedProjectStmt) $totalInvoicedProjectStmt->close();
if ($updateProjectStatusStmt) $updateProjectStatusStmt->close();
if ($razorpayTransactionInsertStmt) $razorpayTransactionInsertStmt->close();

$conn->close();

// Redirect back to the form page
header("Location: bulk-projects-invoice-cess-upload-form.php");
exit();
?>
