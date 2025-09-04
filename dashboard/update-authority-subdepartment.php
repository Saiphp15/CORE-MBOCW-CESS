<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../config/db.php';

// === Error log setup ===
$logFile = __DIR__ . "/../logs/error_log.txt";
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}
ini_set("log_errors", 1);
ini_set("error_log", $logFile);

// === Validate POST ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: authority-subdepartments.php");
    exit;
}

$id           = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$departmentId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
$name         = isset($_POST['name']) ? trim($_POST['name']) : '';
$description  = isset($_POST['description']) ? trim($_POST['description']) : '';
$isActive     = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// === Validation ===
$errors = [];
if ($id <= 0) {
    $errors[] = "Invalid record ID.";
}
if ($departmentId <= 0) {
    $errors[] = "Please select a valid authority department.";
}
if ($name === '') {
    $errors[] = "Sub Department name is required.";
}
if (!in_array($isActive, [1, 2])) {
    $errors[] = "Invalid status value.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: edit-authority-subdepartment.php?id=" . $id);
    exit;
}

// === Transaction ===
try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        UPDATE authority_subdepartments 
        SET department_id=?, name=?, description=?, is_active=?, updated_at=NOW() 
        WHERE id=?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("issii", $departmentId, $name, $description, $isActive, $id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->commit();

    $_SESSION['success'] = "Authority Sub Department updated successfully.";
    header("Location: authority-subdepartments.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("[" . date("Y-m-d H:i:s") . "] Update Error: " . $e->getMessage() . PHP_EOL, 3, $logFile);
    $_SESSION['error'] = "Something went wrong while updating. Please try again.";
    header("Location: edit-authority-subdepartment.php?id=" . $id);
    exit;
}
