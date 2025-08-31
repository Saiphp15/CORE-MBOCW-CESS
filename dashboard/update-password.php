<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page.
    header("Location: ../login.php");
    exit;
}

// Include the database configuration file to establish a connection.
require_once '../config/db.php';

require_once '../vendor/autoload.php'; // Adjust the path if needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize input
    $old_password     = trim($_POST['old_password']);
    $password         = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id          = $_SESSION['user_id'];

    // Basic validation
    if (empty($old_password) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: change-password.php");
        exit;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password and confirm password do not match.";
        header("Location: change-password.php");
        exit;
    }

    // Fetch user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header("Location: change-password.php");
        exit;
    }

    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        $_SESSION['error'] = "Old password does not match.";
        header("Location: change-password.php");
        exit;
    }
    
    // Hash new password
    $hashed_password = md5($password);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password changed successfully! Please login again.";

        // Send email notification
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.yourserver.com'; // change this
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@example.com'; // change this
            $mail->Password   = 'your-email-password';   // change this
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your-email@example.com', 'MBOCWCESS Portal');
            $mail->addAddress($user['email'], $user['name']);
            
            $mail->isHTML(true);
            $mail->Subject = "Password Changed Successfully";
            $mail->Body    = "
                <p>Hi <b>{$user['name']}</b>,</p>
                <p>Your password has been changed successfully on <b>MBOCWCESS Portal</b>.</p>
                <p>If you did not make this change, please contact support immediately.</p>
                <br>
                <p>Regards,<br>MBOCWCESS Team</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        // Force logout after password change
        header("Location: logout.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update password. Please try again.";
        header("Location: change-password.php");
        exit;
    }

} else {
    // If the request method is not POST, redirect to the user list page.
    header("Location: change-password.php");
    exit;
}
?>
