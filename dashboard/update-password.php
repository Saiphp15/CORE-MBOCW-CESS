<?php
/**
 * save-user.php
 *
 * This script processes the user creation form submission from add-user.php.
 * It performs server-side validation, hashes the password,
 * and securely inserts the new user's data into the database,
 * matching the provided column order.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page.
    header("Location: ../login.php");
    exit;
}

// Include the database configuration file to establish a connection.
require_once '../config/db.php';

require_once '../vendor/autoload.php'; // Adjust the path if needed

// Check if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo "<pre>";
    // print_r($_POST);die;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    // echo "<pre>";
    // print_r($user['password']);
    //  echo "\n";
    // print_r(md5('adminpass'));
    // die;
    $old_password = trim($_POST['old_password']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_id = $_SESSION['user_id'];

    // Set a default value for is_active.
    // $is_active_status = 1; 
    // echo "<pre>";
    // print_r($user['password']);
    //  echo "\n";
    // print_r($old_password);
    // die;
    if ($user['password'] != md5($old_password)) {
        $_SESSION['error'] = "Old and New Password does not match";
        header("Location: change-password.php");
        exit;
    }
    // Basic validation check. You can add more complex validation as needed.
    if ($password != $confirm_password) {
        $_SESSION['error'] = "Password and confirm password does not match.";
        header("Location: change-password.php");
        exit;
    }

    // Hash the password for secure storage in the database.
    // $hashed_password = md5($password);

    // --- 2. Database Insertion with Prepared Statements ---
    // Prepare the SQL statement to prevent SQL injection attacks.
    // The column order here now matches the order in your table screenshot.
    $sql = "UPDATE users SET  password = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: edit-profile.php");
        exit;
    }

    // Bind parameters to the prepared statement, ensuring order and data types match.
    // 's' for string, 'i' for integer.
    $stmt->bind_param("si", 
        md5($password), 
        $user_id
    );

    // Execute the statement and check for success.
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";

    } else {
        $_SESSION['error'] = "Failed to add user: " . $stmt->error;
    }

    // Close the statement and the database connection.
    $stmt->close();
    $conn->close();

    // Redirect back to the add user page.
    header("Location: logout.php");
    exit;

} else {
    // If the request method is not POST, redirect to the user list page.
    header("Location: users.php");
    exit;
}
?>
