<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Fetch districts for dropdown
$districts = $conn->query("SELECT * FROM districts WHERE active_status=1");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $district_id = intval($_POST['district_id']);
    $status = intval($_POST['active_status']);

    $sql = "INSERT INTO talukas (name, district_id, active_status, created_by) 
            VALUES ('$name', $district_id, $status, 1)";
    if ($conn->query($sql)) {
        header("Location: talukas.php?msg=Taluka Added");
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

    <title>MBOCWCESS Portal | Add User</title>
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
                            <h1 class="m-0 text-dark">Add User</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Add User</li>
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
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Select District</label>
                                        <select name="district_id" class="form-control" required>
                                            <option value="">--Select District--</option>
                                            <?php while($d = $districts->fetch_assoc()) { ?>
                                                <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="active_status" class="form-control">
                                            <option value="1">Active</option>
                                            <option value="2">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a href="index.php" class="btn btn-secondary">Back</a>
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
