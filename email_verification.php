<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: GET");

// Database connection
require_once 'config/db.php';
require_once 'common/helper.php';


$action = $_POST['action'] ? $_POST['action'] : "";

if( $action == 'email_verification_email_send' ) {
    $fullname = $_POST['full_name'] ?  $_POST['full_name']  : "";
    $email = $_POST['email'] ?  $_POST['email']  : "";

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email address'], 400);
    }

    $password = generatePassword(8, true, true, false);
    $emailSql = "INSERT INTO email_mobile_verification (email, verification_code,is_email, created_at,updated_at)
                            VALUES (?, ?,1, NOW(), NOW())";
    $emailStmt = $conn->prepare($emailSql);
    $emailStmt->bind_param("ss", $email, $password);
    $emailStmt->execute();
    $emailStmt->close();
    sendVerificationEmail($email,$fullname,$password);
    
    echo json_encode(['success' => true, 'message' => 'Verification email sent successfully.']);
    exit;

} else if( $action == 'verify_email' ) {
        $email = $_POST['email'] ?  $_POST['email']  : "";
        $code = $_POST['code'] ?  $_POST['code']  : "";
        if (!$email || !$code) {
            echo json_encode(['error' => 'Invalid email address'], 400);
            exit;
        }

        $sql = "SELECT id FROM email_mobile_verification WHERE email = ? AND verification_code = ? AND is_email = 1 AND is_verified = 0 LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $verification = $result->fetch_assoc();
        if (!$verification) {
            echo json_encode(['error' => 'Invalid verification code.'],400);
            $stmt->close();
            $conn->close();
            exit;
        }

        $verification_id = $verification['id'];
        $sql_update = "UPDATE email_mobile_verification SET is_verified = 1 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $verification_id);

        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'Email verified successfully.']);
        } else {
            echo json_encode(['error' => 'Failed to verify email. Please try again.'], 500);
            exit;
        }

        $stmt->close();
        $stmt_update->close();
        $conn->close();
        exit;

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action specified.']);
    exit;
}
