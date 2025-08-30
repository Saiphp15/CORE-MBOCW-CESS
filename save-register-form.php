<?php
session_start();
require_once 'config/db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Clear previous messages
unset($_SESSION['error']);
unset($_SESSION['success']);
unset($_SESSION['old']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store old input
    $_SESSION['old'] = $_POST;

    $errors = [];

    $local_authority_name = trim($_POST['local_authority_name']);
    $local_authority_type = intval($_POST['local_authority_type']);
    $cafo_name            = trim($_POST['cafo_name']);
    $cafo_email           = filter_var(trim($_POST['cafo_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $cafo_mobile          = trim($_POST['cafo_mobile']);
    $cafo_gender          = trim($_POST['cafo_gender']);
    $cafo_address         = trim($_POST['cafo_address']);
    $aadhaar              = trim($_POST['aadhaar_no']);
    $pan                  = strtoupper(trim($_POST['pan_no']));
    $gstn                 = trim($_POST['gstn']);
    $state_id             = intval($_POST['state'] ?? 0);
    $district_id          = intval($_POST['district'] ?? 0);
    $taluka_id            = intval($_POST['taluka'] ?? 0);
    $village_id           = intval($_POST['village'] ?? 0);

    // ===== Validation =====
    if (empty($local_authority_name) || empty($local_authority_type) || empty($cafo_name) ||
        empty($cafo_email) || empty($cafo_mobile) || empty($cafo_gender) || empty($cafo_address) ||
        empty($aadhaar) || empty($pan)) {
        $_SESSION['error'] = "Please fill all required fields.";
        header("Location: register.php"); exit;
    }

    if (!preg_match('/^\d{12}$/', $aadhaar)) {
        $_SESSION['error'] = "Invalid Aadhaar number.";
        header("Location: register.php"); exit;
    }

    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
        $_SESSION['error'] = "Invalid PAN number.";
        header("Location: register.php"); exit;
    }

    if (!empty($gstn) && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstn)) {
        $_SESSION['error'] = "Invalid GSTN.";
        header("Location: register.php"); exit;
    }

    if (!preg_match('/^\d{10}$/', $cafo_mobile)) {
        $_SESSION['error'] = "Invalid Mobile Number.";
        header("Location: register.php"); exit;
    }

    $conn->begin_transaction();

    try {

        // Duplicate check
        if (!empty($gstn)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR phone=? OR aadhaar=? OR pancard=? OR gstn=? LIMIT 1");
            $stmt->bind_param("sssss", $cafo_email, $cafo_mobile, $aadhaar, $pan, $gstn);
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR phone=? OR aadhaar=? OR pancard=? LIMIT 1");
            $stmt->bind_param("ssss", $cafo_email, $cafo_mobile, $aadhaar, $pan);
        }
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            throw new Exception("Duplicate User (Email / Mobile / Aadhaar / PAN / GSTN).");
        }
        $stmt->close();

        // Insert into users
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, gender, state_id, district_id, taluka_id, village_id, address, role, gstn, pancard, aadhaar, is_active) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $password = md5("123456"); 
        $role = 3; // CAFO role 
        $is_active = 2; // Set to 2 (Inactive) by default
        $local_authority_id = 0; 

        $stmt->bind_param("sssssiiiisisssi", $cafo_name, $cafo_email, $password, $cafo_mobile, $cafo_gender, $state_id, $district_id, $taluka_id, $village_id, $cafo_address, $role, $gstn, $pan, $aadhaar, $is_active);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Duplicate check
            $stmt = $conn->prepare("SELECT id FROM local_authorities WHERE type_id=? AND name=? AND district_id=? LIMIT 1");
            $stmt->bind_param("isi", $local_authority_type, $local_authority_name, $district_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                throw new Exception("Duplicate Local Authority (local_authority_type / local_authority_name / district_id).");
            }else{
                $stmt = $conn->prepare("INSERT INTO local_authorities (type_id, name, state_id, district_id, taluka_id, village_id, address, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isiiiisi", $local_authority_type, $local_authority_name, $state_id, $district_id, $taluka_id, $village_id, $cafo_address, $is_active);

                if ($stmt->execute()) {
                    $local_authority_id = $stmt->insert_id;
                    $stmt->close();

                    // Duplicate check for local_authorities_users
                    $checkSql = "SELECT id FROM local_authorities_users WHERE local_authority_id=? AND user_id=? LIMIT 1";
                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->bind_param("ii", $local_authority_id, $user_id);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows > 0) {
                        throw new Exception("Duplicate entry: This user is already assigned to the selected Local Authority.");
                    }
                    $checkStmt->close();

                    // Insert into local_authorities_users
                    $sql2 = "INSERT INTO local_authorities_users (local_authority_id, user_id, is_active)
                            VALUES (?, ?, ?)";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("iii", $local_authority_id, $user_id, $is_active);
                    $stmt2->execute();
                    $stmt2->close();

                    // --- Start of email sending code ---
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'aaravprashantmane@gmail.com'; // Replace with your email
                        $mail->Password   = 'rpfbzhzfxomebmcq'; // Replace with your email password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Recipients
                        $mail->setFrom('aaravprashantmane@gmail.com', 'MBOCW CESS Portal');
                        $mail->addAddress($cafo_email, $cafo_name);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Welcome to MBOCW CESS Portal!';
                        $mail->Body    = '
                            <p>Hello ' . htmlspecialchars($cafo_name) . ',</p>
                            <p>Welcome! Your account has been created successfully. Your login details are:</p>
                            <p><strong>Link:</strong> <a href="http://localhost/CORE-MBOCW-CESS/login.php">click here to login</a></p>
                            <p><strong>Username:</strong> ' . htmlspecialchars($cafo_email) . '</p>
                            <p><strong>Password:</strong> 123456</p>
                            <p>Please log in and change your password as soon as possible for security reasons.</p>
                            <p>Thank you,</p>
                            <p>The MBOCW CESS Team</p>
                        ';
                        $mail->AltBody = 'Hello ' . htmlspecialchars($cafo_name) . ",\n\nWelcome! Your account has been created successfully. Your login details are:\nUsername: " . htmlspecialchars($cafo_email) . "\nPassword: 123456\n\nPlease log in and change your password as soon as possible for security reasons.\n\nThank you,\nThe MBOCW CESS Team";

                        $mail->send();
                    } catch (Exception $e) {
                        // Log the error but don't stop the registration process
                        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
                    }
                    // --- End of email sending code ---

                    $conn->commit();
                    $_SESSION['success'] = "Registration successful!";
                    unset($_SESSION['old']); // clear old inputs
                } else {
                    throw new Exception("Failed to save Local Authority.");
                }
            }
        } else {
            throw new Exception("Failed to save User.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: register.php"); exit;
}

?>