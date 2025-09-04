<?php
require_once '../config/db.php';

$department_id = intval($_GET['department_id']);
$query = $conn->prepare("SELECT id, name FROM authority_subdepartments WHERE department_id = ?");
$query->bind_param("i", $department_id);
$query->execute();
$result = $query->get_result();

echo '<option value="">-- Select Sub Department --</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
}
