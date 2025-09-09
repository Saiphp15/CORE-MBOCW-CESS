<?php
session_start();
// Assuming you have a database connection file like db.php
require_once '../config/db.php';

require_once '../vendor/autoload.php'; // Adjust the path if needed
use PHPMailer\PHPMailer\PHPMailer;

// Clear previous messages
unset($_SESSION['error']);
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Save form data to session in case of an error
    $_SESSION['old_data'] = $_POST;

    $errors = [];

    // Sanitize and validate input
    $employer_type = trim($_POST['employer_type'] ?? '');
    $name          = trim($_POST['name'] ?? '');
    $email         = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone         = trim($_POST['phone'] ?? '');
    $pancard       = strtoupper(trim($_POST['pancard'] ?? ''));
    $aadhaar       = trim($_POST['aadhaar'] ?? '');
    $gstn          = strtoupper(trim($_POST['gstn'] ?? ''));
    $is_active     = 1; // Default to active

    // File paths
    $pan_path    = '';
    $aadhaar_path = '';

    // Basic server-side validation
    if (empty($employer_type) || empty($name) || empty($email) || empty($phone) || empty($pancard) || empty($aadhaar) || !isset($_FILES['pancard_path']) || !isset($_FILES['aadhaar_path'])) {
        $errors[] = "Please fill all required fields, including file uploads.";
    }

    if (!$email) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Invalid phone number.";
    }

    // PAN card validation
    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pancard)) {
        $errors[] = "Invalid PAN number format.";
    }

    // Aadhaar validation
    if (!preg_match('/^[0-9]{12}$/', $aadhaar)) {
        $errors[] = "Invalid Aadhaar number format.";
    }

    // GSTN validation (optional)
    if (!empty($gstn) && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstn)) {
        $errors[] = "Invalid GSTN format.";
    }

    // File upload validation and handling
    if (empty($errors)) {
        // PAN file upload
        if ($_FILES['pancard_path']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "PAN Card file upload failed with error code: " . $_FILES['pancard_path']['error'];
        } else {
            $file_ext = pathinfo($_FILES['pancard_path']['name'], PATHINFO_EXTENSION);
            $new_file_name = $pancard . '_' . time() . '.' . $file_ext;
            $upload_dir = '../uploads/employer_pan/';
            $pan_path = $upload_dir . $new_file_name;
            if (!move_uploaded_file($_FILES['pancard_path']['tmp_name'], $pan_path)) {
                $errors[] = "Failed to move uploaded PAN Card file.";
            }
        }

        // Aadhaar file upload
        if ($_FILES['aadhaar_path']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Aadhaar Card file upload failed with error code: " . $_FILES['aadhaar_path']['error'];
        } else {
            $file_ext = pathinfo($_FILES['aadhaar_path']['name'], PATHINFO_EXTENSION);
            $new_file_name = $aadhaar . '_' . time() . '.' . $file_ext;
            $upload_dir = '../uploads/employer_aadhaar/';
            $aadhaar_path = $upload_dir . $new_file_name;
            if (!move_uploaded_file($_FILES['aadhaar_path']['tmp_name'], $aadhaar_path)) {
                $errors[] = "Failed to move uploaded Aadhaar Card file.";
            }
        }
    }


    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: add-employer.php");
        exit();
    }

    // Transaction start
    $conn->begin_transaction();

    try {
        // Check for duplicate records in the `employers` table
        $checkSql = "SELECT id FROM employers WHERE email = ? OR phone = ? OR aadhaar = ? OR pancard = ?";
        if (!empty($gstn)) {
            $checkSql .= " OR gstn = ?";
        }
        $checkStmt = $conn->prepare($checkSql);

        if (!empty($gstn)) {
            $checkStmt->bind_param("sssss", $email, $phone, $aadhaar, $pancard, $gstn);
        } else {
            $checkStmt->bind_param("ssss", $email, $phone, $aadhaar, $pancard);
        }

        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            throw new Exception("Duplicate record found. An employer with this email, phone, Aadhaar, PAN, or GSTN already exists.");
        }
        $checkStmt->close();

        $loggedInUserId = $_SESSION['user_id'] ?? 0;

        // Insert new employer
        $sql = "INSERT INTO `employers` (`employer_type`, `name`, `email`, `phone`, `pancard`, `aadhaar`, `gstn`, `pancard_path`, `aadhaar_path`, `is_active`, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Bind parameters. The 's' in "sssssssi" stands for string. The last 'i' is for integer (is_active).
        $stmt->bind_param("sssssssssii", $employer_type, $name, $email, $phone, $pancard, $aadhaar, $gstn, $pan_path, $aadhaar_path, $is_active, $loggedInUserId);

        if ($stmt->execute()) {
            $conn->commit();
            // Unset old data from session on success
            unset($_SESSION['old_data']);
            $_SESSION['success'] = "Employer added successfully!";

            // Send Welcome Email ---
            $mail = new PHPMailer(true);
            try {
                // Server settings for Gmail SMTP
                //$mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Uncomment for detailed debugging
                $mail->isSMTP();                                           
                $mail->Host       = 'smtp.gmail.com';                     // Your SMTP server
                $mail->SMTPAuth   = true;                                   
                $mail->Username   = 'aaravprashantmane@gmail.com';               // Your SMTP username
                $mail->Password   = 'rpfbzhzfxomebmcq';                  // Your SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Or ENCRYPTION_SMTPS
                $mail->Port       = 587;                                    // Or 465 for SMTPS

                // Recipients
                $mail->setFrom('aaravprashantmane@gmail.com', 'MBOCWCESS');
                $mail->addAddress($email, $name);     // Add a recipient

                // Content
                $mail->isHTML(true);                                  
                $mail->Subject = 'Welcome to MBOCW CESS Portal!';

                // This is the message for users who do not require a login.
                $mail->Body    = "
                <p>Hello **$name**,</p>
                <p>Your profile has been created successfully as a Employer in our system.</p>
                <br>
                <p>Best regards,</p>
                <p>MBOCW CESS Team</p>
                ";
                $mail->send();
                $_SESSION['success'] .= " A welcome email has been sent.";
            } catch (Exception $e) {
                // Log the error but don't stop the script.
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                $_SESSION['error'] = "Employer added, but the welcome email could not be sent. Please check mailer settings.";
            }

        } else {
            throw new Exception("Error adding employer: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        // Delete uploaded files if transaction fails
        if (file_exists($pan_path)) unlink($pan_path);
        if (file_exists($aadhaar_path)) unlink($aadhaar_path);
        $_SESSION['error'] = $e->getMessage();
    }

    $conn->close();
    header("Location: add-employer.php");
    exit();
} else {
    header("Location: add-employer.php");
    exit();
}
?>
