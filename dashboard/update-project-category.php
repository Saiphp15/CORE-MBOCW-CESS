<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = intval($_POST['id']);
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $is_active   = intval($_POST['is_active']);

    if (empty($name)) {
        $_SESSION['error'] = "Category Name is required";
        header("Location: edit-project-category.php?id=$id");
        exit;
    }

    $stmt = $conn->prepare("UPDATE project_categories 
                            SET name=?, description=?, is_active=?, updated_at=NOW() 
                            WHERE id=?");
    $stmt->bind_param("ssii", $name, $description, $is_active, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Project Category updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating category: " . $conn->error;
    }
    $stmt->close();
    header("Location: edit-project-category.php?id=$id");
    exit;
}
