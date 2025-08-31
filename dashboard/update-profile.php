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
use PHPMailer\PHPMailer\PHPMailer;

// Check if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo "<pre>";
    // print_r($_POST);die;
    // --- 1. Server-Side Validation ---
    // Sanitize and validate all incoming form data.
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    // $password = '123456'; // Default password for new users
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    $state_id = filter_var($_POST['state_id'], FILTER_SANITIZE_NUMBER_INT);
    $district_id = filter_var($_POST['district_id'], FILTER_SANITIZE_NUMBER_INT);
    $taluka_id = filter_var($_POST['taluka_id'], FILTER_SANITIZE_NUMBER_INT);
    $village_id = filter_var($_POST['village_id'] ?? 1, FILTER_SANITIZE_NUMBER_INT);
    $address = trim($_POST['address']);
    // $role_id = filter_var($_POST['role'], FILTER_SANITIZE_NUMBER_INT);
    $gstn = trim($_POST['gstn']);
    $pancard = trim($_POST['pancard']);
    $aadhaar = trim($_POST['aadhaar']);
    $user_id = trim($_POST['id']);

    // Set a default value for is_active.
    // $is_active_status = 1; 

    // Basic validation check. You can add more complex validation as needed.
    if (empty($name) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "Please fill in all required fields (Name, Email, Phone).";
        header("Location: edit-profile.php");
        exit;
    }

    // Hash the password for secure storage in the database.
    // $hashed_password = md5($password);

    // --- 2. Database Insertion with Prepared Statements ---
    // Prepare the SQL statement to prevent SQL injection attacks.
    // The column order here now matches the order in your table screenshot.
    $sql = "UPDATE users SET
        name = ?,
        email = ?,
        phone = ?,
        gender = ?,
        state_id = ?,
        district_id = ?,
        taluka_id = ?,
        village_id = ?,
        address = ?,
        gstn = ?,
        pancard = ?,
        aadhaar = ?
    WHERE id = ?;";
    
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: edit-profile.php");
        exit;
    }

    // Bind parameters to the prepared statement, ensuring order and data types match.
    // 's' for string, 'i' for integer.
    $stmt->bind_param("ssssiiiissssi", 
        $name, 
        $email, 
        $phone, 
        $gender, 
        $state_id, 
        $district_id, 
        $taluka_id, 
        $village_id, 
        $address,
        $gstn, 
        $pancard, 
        $aadhaar,
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
    header("Location: edit-profile.php");
    exit;

} else {
    // If the request method is not POST, redirect to the user list page.
    header("Location: users.php");
    exit;
}
?>
