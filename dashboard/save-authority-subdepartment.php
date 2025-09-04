<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name          = trim($_POST['name'] ?? '');
    $department_id = trim($_POST['department_id'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    // Store old values in case of error
    $_SESSION['old_values'] = $_POST;

    try {
        // Validation
        if (empty($name)) {
            throw new Exception("Authority Sub Department name is required.");
        }

        if (empty($department_id)) {
            throw new Exception("Authority Department is required.");
        }

        // Begin transaction
        $conn->begin_transaction();

        // Check for duplicate department name
        $check_stmt = $conn->prepare("SELECT id FROM authority_subdepartments WHERE name = ? AND is_active != 3 LIMIT 1");
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            throw new Exception("An Authority Sub Department with this name already exists.");
        }

        // Insert new record
        $stmt = $conn->prepare("INSERT INTO authority_subdepartments (department_id, name, description, is_active, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
        $stmt->bind_param("iss", $department_id,$name, $description);

        if (!$stmt->execute()) {
            throw new Exception("Failed to save Authority Department: " . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        unset($_SESSION['old_values']); // clear old form values
        $_SESSION['success'] = "Authority Sub Department added successfully.";
        header("Location: authority-subdepartments.php"); exit;

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Error in save-subauthority-department.php: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: add-authority-subdepartment.php"); exit;
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: add-authority-subdepartment.php");
    exit;
}
