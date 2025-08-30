<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// fetch record
$sql = "SELECT la.*, 
               at.name AS authority_type_name, 
               s.name AS state_name, 
               d.name AS district_name, 
               t.name AS taluka_name, 
               v.name AS village_name
        FROM local_authorities la
        LEFT JOIN authority_types at ON la.authority_type_id = at.id
        LEFT JOIN states s ON la.state_id = s.id
        LEFT JOIN districts d ON la.district_id = d.id
        LEFT JOIN talukas t ON la.taluka_id = t.id
        LEFT JOIN villages v ON la.village_id = v.id
        WHERE la.id = $id";

$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Authority</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-4">

    <h3>Basic Information</h3>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Authority Name</label>
                <p class="form-control-plaintext"><?= htmlspecialchars($row['name']); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Authority Type</label>
                <p class="form-control-plaintext"><?= htmlspecialchars($row['authority_type_name']); ?></p>
            </div>
        </div>
    </div>

    <h3>Authority Location</h3>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>State</label>
                <p class="form-control-plaintext"><?= htmlspecialchars($row['state_name']); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>District</label>
                <p class="form-control-plaintext"><?= htmlspecialchars($row['district_name']); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Taluka</label>
                <p class="form-control-plaintext"><?= htmlspecialchars($row['taluka_name']); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Village</label>
                <p class="form-control-plaintext"><?= htmlspecialchars($row['village_name']); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Address</label>
                <p class="form-control-plaintext"><?= nl2br(htmlspecialchars($row['address'])); ?></p>
            </div>
        </div>
    </div>

    <br/>
    <a href="local-authorities.php" class="btn btn-secondary">Back</a>
</body>
</html>
