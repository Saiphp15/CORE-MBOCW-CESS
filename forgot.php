<?php
session_start();
require_once 'config/db.php'; // connection to your MySQL DB
require_once 'common/emailVerification.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $_SESSION['error'] = "Please enter email.";
        header("Location: forgot_password.php");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.password, u.role, r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role = r.id
        WHERE u.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $name, $userEmail, $hashedPassword, $role, $roleName);
        $stmt->fetch();
        
        $token = bin2hex(random_bytes(50));
        $sql = "UPDATE users SET token=?, created_at=NOW() WHERE id=$id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s",$token);
        if($stmt->execute()){
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['SCRIPT_NAME']);
            $reset_link = "{$protocol}://{$host}{$path}/reset_password.php?token={$token}";
            sendForgotPassword('ensight.jayanti@gmail.com',$fullname,$reset_link);
            
        }
        $_SESSION['success'] = "Reset password link send suceessfully !";
        header("Location: forgot_password.php");
    } else {
        $_SESSION['error'] = "No user found with this email.";
        header("Location: forgot_password.php");
        exit;
    }
} else {
    header("Location: forgot_password.php");
    exit;
}
?>
