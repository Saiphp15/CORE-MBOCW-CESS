<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/db.php';
// require_once 'common/emailVerification.php';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $token = trim($_POST["token"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirm_password"]);
    
    if (empty($password) || empty($confirm)) {
        $_SESSION['error'] = "Please enter both Password and Confirm password.";
        header("Location: reset_password.php?token=" . $token);
        exit;
    }

    if ($password != $confirm) {
        $_SESSION['error'] = "Password and confirm password are not same.";
        header("Location: reset_password.php?token=" . $token);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.password, u.role, r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role = r.id
        WHERE u.email = ? AND u.token=?
    ");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $name, $userEmail, $hashedPassword, $role, $roleName);
        $stmt->fetch();
        $passwordNew = md5($password);
        $sql = "UPDATE users SET token=null, updated_at=NOW(), password=? WHERE id=$id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s",$passwordNew);
        if($stmt->execute()){
            $_SESSION['success'] = "Reset password link send suceessfully !";
            header("Location: login.php");
        }
        
        $stmt->close();
    } else {
        $_SESSION['error'] = "Token is missmatch! Please again.";
        header("Location: forgot_password.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Token is missmatch! Please again.";
    header("Location: forgot_password.php");
    exit;
}
?>
