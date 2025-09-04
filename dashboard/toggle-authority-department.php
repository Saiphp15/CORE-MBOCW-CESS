<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['status'])) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }

    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    $stmt = $conn->prepare("UPDATE authority_departments SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

?>
