<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') { header("Location: project-types.php"); exit; }

$id               = intval($_POST['id'] ?? 0);
$category_id      = intval($_POST['category_id'] ?? 0);
$name             = trim($_POST['name'] ?? '');
$description      = trim($_POST['description'] ?? '');
$cess_trigger     = trim($_POST['cess_trigger'] ?? '');
$how_cess_is_paid = trim($_POST['how_cess_is_paid'] ?? '');
$is_active        = intval($_POST['is_active'] ?? 1);

if ($id<=0 || $category_id<=0 || $name==='') {
  $_SESSION['error'] = "Category, Name are required.";
  header("Location: edit-project-type.php?id=".$id); exit;
}

$stmt = $conn->prepare("UPDATE project_types
                        SET category_id=?, name=?, description=?, cess_trigger=?, how_cess_is_paid=?, is_active=?, updated_at=NOW()
                        WHERE id=?");
$stmt->bind_param("issssii", $category_id, $name, $description, $cess_trigger, $how_cess_is_paid, $is_active, $id);

if ($stmt->execute()) {
  $_SESSION['success'] = "Project Type updated successfully.";
} else {
  $_SESSION['error'] = "Update failed: " . $conn->error;
}
$stmt->close();

header("Location: edit-project-type.php?id=".$id);
exit;
