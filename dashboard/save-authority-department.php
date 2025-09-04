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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? null);

    // Store old values in case of error
    $_SESSION['old_values'] = $_POST;

    try {
        // Validation
        if (empty($name)) {
            throw new Exception("Authority Department name is required.");
        }

        // Begin transaction
        $conn->begin_transaction();

        // Check for duplicate department name
        $check_stmt = $conn->prepare("SELECT id FROM authority_departments WHERE name = ? AND is_active != 3 LIMIT 1");
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            throw new Exception("An Authority Department with this name already exists.");
        }

        // Insert new record
        $stmt = $conn->prepare("INSERT INTO authority_departments (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
        $stmt->bind_param("ss", $name, $description);

        if (!$stmt->execute()) {
            throw new Exception("Failed to save Authority Department: " . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        unset($_SESSION['old_values']); // clear old form values
        $_SESSION['success'] = "Authority Department added successfully.";
        header("Location: authority-departments.php"); exit;

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Error in save-authority-department.php: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: add-authority-department.php"); exit;
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: add-authority-department.php");
    exit;
}
