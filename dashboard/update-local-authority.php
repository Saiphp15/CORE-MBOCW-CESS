<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../config/db.php';

// --- Validate request ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: local-authorities.php");
    exit;
}

$authority_id = (int) $_POST['id'];

// --- Sanitize inputs ---
$name = trim($_POST['name'] ?? '');
$authority_type_id = (int) ($_POST['authority_type_id'] ?? 0);
$department_id = (int) ($_POST['department_id'] ?? 0);
$subdepartment_id = (int) ($_POST['subdepartment_id'] ?? 0);
$pancard = strtoupper(trim($_POST['pancard'] ?? ''));
$aadhaar = trim($_POST['aadhaar'] ?? '');
$gstn = strtoupper(trim($_POST['gstn'] ?? ''));
$state_id = (int) ($_POST['state_id'] ?? 0);
$district_id = (int) ($_POST['district_id'] ?? 0);
$taluka_id = (int) ($_POST['taluka_id'] ?? 0);
$village_id = (int) ($_POST['village_id'] ?? 0);
$address = trim($_POST['address'] ?? '');
$user_id = (int) ($_POST['user_id'] ?? 0);

// --- Validations ---
$errors = [];
if ($pancard && !preg_match("/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/", $pancard)) {
    $errors[] = "Invalid PAN Card number.";
}
if ($aadhaar && !preg_match("/^[2-9]{1}[0-9]{11}$/", $aadhaar)) {
    $errors[] = "Invalid Aadhaar number.";
}
if ($gstn && !preg_match("/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/", $gstn)) {
    $errors[] = "Invalid GSTN.";
}

if ($errors) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: edit-local-authority.php?id=" . $authority_id);
    exit;
}

// --- File Upload handling ---
$upload_dir = "../uploads/local_authorities/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$pancard_path = null;
$aadhaar_path = null;

// PAN card file
if (!empty($_FILES['pancard_path']['name'])) {
    $ext = pathinfo($_FILES['pancard_path']['name'], PATHINFO_EXTENSION);
    $filename = "pan_" . $authority_id . "_" . time() . "." . $ext;
    $target = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['pancard_path']['tmp_name'], $target)) {
        $pancard_path = "uploads/local_authorities/" . $filename;
    }
}

// Aadhaar file
if (!empty($_FILES['aadhaar_path']['name'])) {
    $ext = pathinfo($_FILES['aadhaar_path']['name'], PATHINFO_EXTENSION);
    $filename = "aadhaar_" . $authority_id . "_" . time() . "." . $ext;
    $target = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['aadhaar_path']['tmp_name'], $target)) {
        $aadhaar_path = "uploads/local_authorities/" . $filename;
    }
}

try {
    // Start transaction
    $conn->begin_transaction();

    // --- Build SQL for updating local_authorities ---
    $sql = "UPDATE local_authorities SET 
                name = ?, 
                type_id = ?, 
                authority_department_id = ?, 
                authority_subdepartment_id = ?, 
                pancard = ?, 
                aadhaar = ?, 
                gstn = ?, 
                state_id = ?, 
                district_id = ?, 
                taluka_id = ?, 
                village_id = ?, 
                address = ?";

    $params = [
        $name, $authority_type_id, $department_id, $subdepartment_id,
        $pancard, $aadhaar, $gstn,
        $state_id, $district_id, $taluka_id, $village_id, $address
    ];
    $types = "siiiissiiiis";

    if ($pancard_path) {
        $sql .= ", pancard_path = ?";
        $params[] = $pancard_path;
        $types .= "s";
    }
    if ($aadhaar_path) {
        $sql .= ", aadhaar_path = ?";
        $params[] = $aadhaar_path;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $authority_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // --- Handle local_authorities_users (CAFO) ---
        $stmt->close();

        // Remove old mapping
        $conn->query("DELETE FROM local_authorities_users WHERE local_authority_id = " . $authority_id);

        if ($user_id) {
            $stmt2 = $conn->prepare("INSERT INTO local_authorities_users (local_authority_id, user_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $authority_id, $user_id);
            $stmt2->execute();
            $stmt2->close();
        }

        $_SESSION['success'] = "Local authority updated successfully.";
        header("Location: edit-local-authority.php?id=" . $authority_id);
        exit;

    } else {
        $_SESSION['error'] = "Error updating authority: " . $stmt->error;
        header("Location: edit-local-authority.php?id=" . $authority_id);
        exit;
    }

} catch (Exception $e) {
    // Rollback on error
    $conn->rollBack();
    
    // Error logging
    $errorMessage = date("Y-m-d H:i:s") . " - Error updating CAFO: " . $e->getMessage() . PHP_EOL;
    file_put_contents(__DIR__ . "/../logs/error_log.txt", $errorMessage, FILE_APPEND);

    echo "Failed to update CAFO. Please check logs.";
}
