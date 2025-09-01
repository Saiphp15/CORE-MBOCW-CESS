<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

$id = intval($_GET['id']);
$taluka = $conn->query("SELECT * FROM talukas WHERE id=$id")->fetch_assoc();
$districts = $conn->query("SELECT * FROM districts WHERE active_status=1");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $district_id = intval($_POST['district_id']);
    $status = intval($_POST['active_status']);

    $sql = "UPDATE talukas 
            SET name='$name', district_id=$district_id, active_status=$status, updated_by=1 
            WHERE id=$id";
    if ($conn->query($sql)) {
        header("Location: index.php?msg=Taluka Updated");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>MBOCWCESS Portal | View Taluka</title>
    <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <?php include('includes/navbar.php'); ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php include('includes/sidebar.php'); ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark">View Taluka</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">View Taluka</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->
            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="row">
                                  <div class="col-md-12 ">
                                <form method="post">
                                    <div class="form-group">
                                        <label>Taluka Name</label>
                                        <input type="text" name="name" class="form-control" 
                                            value="<?= htmlspecialchars($taluka['name']) ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Select District</label>
                                        <select name="district_id" class="form-control" readonly>
                                            <?php while($d = $districts->fetch_assoc()) { ?>
                                                <option value="<?= $d['id'] ?>" <?= $d['id'] == $taluka['district_id'] ? "selected" : "" ?>>
                                                    <?= $d['name'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="active_status" class="form-control" readonly>
                                            <option value="1" <?= $taluka['active_status']==1 ? "selected" : "" ?>>Active</option>
                                            <option value="2" <?= $taluka['active_status']==2 ? "selected" : "" ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <a href="talukas.php" class="btn btn-secondary">Back</a>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
