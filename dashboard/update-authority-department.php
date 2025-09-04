<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: authority-departments.php");
    exit;
}

$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

// Basic validation
if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Invalid department ID.";
    header("Location: authority-departments.php");
    exit;
}

if (empty($name)) {
    $_SESSION['error'] = "Authority Department Name is required.";
    header("Location: edit-authority-department.php?id=" . urlencode($id));
    exit;
}

try {
    $conn->begin_transaction();

    // Check if department exists
    $stmt = $conn->prepare("SELECT id FROM authority_departments WHERE id = ? AND is_active != 3 LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        $conn->rollback();
        $_SESSION['error'] = "Department not found.";
        header("Location: authority-departments.php");
        exit;
    }
    $stmt->close();

    // Update department
    $stmt = $conn->prepare("UPDATE authority_departments 
                            SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP 
                            WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);

    if ($stmt->execute()) {
        $conn->commit();
        $_SESSION['success'] = "Authority Department updated successfully.";
    } else {
        $conn->rollback();
        $_SESSION['error'] = "Failed to update Authority Department.";
    }
    $stmt->close();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: authority-departments.php");
exit;
