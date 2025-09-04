<?php
session_start();
require_once "../config/db.php"; // your DB connection

$logFile = __DIR__ . "/../logs/error_log.txt";
function logError($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

$_SESSION['old_values'] = $_POST;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name'] ?? '');
    $type_id    = $_POST['authority_type_id'] ?? null;
    $dept_id    = $_POST['department_id'] ?? null;
    $subdept_id = $_POST['subdepartment_id'] ?? null;
    $state_id   = $_POST['state_id'] ?? null;
    $district_id= $_POST['district_id'] ?? null;
    $taluka_id  = !empty($_POST['taluka_id']) ? $_POST['taluka_id'] : null;
    $village_id = !empty($_POST['village_id']) ? $_POST['village_id'] : null;
    $address    = trim($_POST['address'] ?? '');
    $user_id    = $_POST['user_id'] ?? null;

    $pancard        = trim($_POST['pancard'] ?? '');
    $aadhaar    = trim($_POST['aadhaar'] ?? '');
    $gstn       = trim($_POST['gstn'] ?? '');

    // ---------------- Validation ----------------
    if (empty($name)) {
        $errors[] = "Authority name is required.";
    }

    if (empty($type_id) || !ctype_digit($type_id)) {
        $errors[] = "Valid authority type is required.";
    }

    if (empty($dept_id) || !ctype_digit($dept_id)) {
        $errors[] = "Valid department is required.";
    }

    if (empty($subdept_id) || !ctype_digit($subdept_id)) {
        $errors[] = "Valid subdepartment is required.";
    }

    // Validation functions
    function validatePAN($pancard) {
        return preg_match("/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/", $pancard);
    }
    function validateAadhaar($aadhaar) {
        return preg_match("/^[2-9]{1}[0-9]{11}$/", $aadhaar);
    }
    function validateGSTIN($gst) {
        return preg_match("/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/", $gst);
    }
    if ($pancard && !validatePAN($pancard)) {
        $errors[] = "Invalid PAN format.";
    }
        
    if ($aadhaar && !validateAadhaar($aadhaar)) {
        $errors[] = "Invalid Aadhaar format.";
    }
    
    if ($gst && !validateGSTIN($gst)) {
        $errors[] = "Invalid GSTIN format.";
    }

    if (empty($state_id) || !ctype_digit($state_id)) {
        $errors[] = "Valid state is required.";
    }

    if (empty($district_id) || !ctype_digit($district_id)) {
        $errors[] = "Valid district is required.";
    }

    if ($taluka_id !== null && !ctype_digit($taluka_id)) {
        $errors[] = "Invalid taluka.";
    }
    if ($village_id !== null && !ctype_digit($village_id)) {
        $errors[] = "Invalid village.";
    }

    $address = trim($_POST['address'] ?? '');
    if (strlen($address) > 255) {
        $errors[] = "Address too long (max 255 chars).";
    }

    if (empty($_POST['user_id']) || !ctype_digit($_POST['user_id'])) {
        $errors[] = "Valid user (CAFO) is required.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: add-local-authority.php");
        exit;
    }

    // ---------------- File Uploads ----------------
    $panPath = null;
    $aadhaarPath = null;

    function uploadFile($field, $folder) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
        $allowed = ['jpg','jpeg','png','pdf'];
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            throw new Exception("Invalid file type for $field");
        }
        $dir = __DIR__ . "/../uploads/local_authorities/$folder/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $newName = uniqid($field . "_") . "." . $ext;
        $dest = $dir . $newName;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
            throw new Exception("Failed to upload $field");
        }
        return "uploads/local_authorities/$folder/" . $newName;
    }

    try {
        $panPath     = uploadFile('pancard_path', 'pan_cards');
        $aadhaarPath = uploadFile('aadhaar_path', 'aadhaar_cards');
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    // ---------------- Insert with Transaction ----------------
    try {
        $conn->begin_transaction();

        // Insert into local_authorities
        $sql = "INSERT INTO local_authorities 
                (name, type_id, authority_department_id, authority_subdepartment_id, 
                 state_id, district_id, taluka_id, village_id, address, 
                 pancard, pancard_path, aadhaar, aadhaar_path, gstn, 
                 is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "siiiiiiissssss",
            $name, $type_id, $dept_id, $subdept_id,
            $state_id, $district_id, $taluka_id, $village_id,
            $address, $pancard, $panPath, $aadhaar, $aadhaarPath, $gstn
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert authority failed: " . $stmt->error);
        }

        $local_authority_id = $stmt->insert_id;
        $stmt->close();

        // Insert mapping into local_authorities_users
        $sql = "INSERT INTO local_authorities_users 
                (local_authority_id, user_id, is_active, created_at, updated_at)
                VALUES (?, ?, 1, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ii", $local_authority_id, $_POST['user_id']);

        if (!$stmt->execute()) {
            throw new Exception("Insert mapping failed: " . $stmt->error);
        }

        $stmt->close();
        $conn->commit();

        unset($_SESSION['old_values']);
        $_SESSION['success'] = "Local authority and mapping saved successfully.";
        header("Location: local-authorities.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        logError("Error saving authority: " . $e->getMessage());
        $_SESSION['error'] = "Something went wrong while saving. Please try again.";
        header("Location: add-local-authority.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: add-local-authority.php");
    exit;
}