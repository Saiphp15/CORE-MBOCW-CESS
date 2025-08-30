<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit;
}
require_once '../config/db.php';
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//    
    // Collect data safely
    $id              = isset($_POST['id']) ? intval($_POST['id']) : 0; // Hidden field for record ID
    $authority_name  = mysqli_real_escape_string($conn, $_POST['name']);
    $authority_type  = mysqli_real_escape_string($conn, $_POST['type_id']);
    $state_id        = mysqli_real_escape_string($conn, $_POST['state_id']);
    $district_id     = mysqli_real_escape_string($conn, $_POST['district_id']);
    $taluka_id       = mysqli_real_escape_string($conn, $_POST['taluka_id']);
    $village_id      = mysqli_real_escape_string($conn, $_POST['village_id']);
    $address         = mysqli_real_escape_string($conn, $_POST['address']);
    $status = 1;

    // echo '<pre>'; print_r($_POST); die(); 
    // Validate ID
    if ($id > 0) {
        $sql = "UPDATE local_authorities 
                SET name = '$authority_name',
                    type_id = '$authority_type',
                    state_id = '$state_id',
                    district_id = '$district_id',
                    taluka_id = '$taluka_id',
                    village_id = '$village_id',
                    address = '$address',
                    is_active = '$status',
                    updated_at = NOW()
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            // Redirect with success message
            header("Location: local-authorities.php?msg=updated");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid Authority ID.";
    }
} else {
    echo "Invalid Request.";
}
