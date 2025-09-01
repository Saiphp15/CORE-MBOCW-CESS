<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = intval($_POST['id']);
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender   = $_POST['gender'];
    $role     = $_POST['role'];
    $gstn     = $_POST['gstn'];
    $pancard  = $_POST['pancard'];
    $aadhaar  = $_POST['aadhaar'];
    $state    = $_POST['state_id'];
    $district = $_POST['district_id'];
    $taluka   = $_POST['taluka_id'];
    $village  = $_POST['village_id'];
    $address  = mysqli_real_escape_string($conn, $_POST['address']);

    $sql = "UPDATE users SET 
                name='$name',
                email='$email',
                phone='$phone',
                gender='$gender',
                role='$role',
                gstn='$gstn',
                pancard='$pancard',
                aadhaar='$aadhaar',
                state_id='$state',
                district_id='$district',
                taluka_id='$taluka',
                village_id='$village',
                address='$address'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "User updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating user: " . mysqli_error($conn);
    }

    header("Location: users.php");
    exit;
}
?>
