<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Get User ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user data
$query = "SELECT * FROM users WHERE id = $id LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Fetch roles for display
$rolesQuery = "SELECT * FROM roles";
$rolesResult = mysqli_query($conn, $rolesQuery);
$roles = [];
while ($row = mysqli_fetch_assoc($rolesResult)) {
    $roles[] = $row;
}

// Fetch districts for display
$districtQuery = "SELECT * FROM districts";
$districtResult = mysqli_query($conn, $districtQuery);
$districts = [];
while ($row = mysqli_fetch_assoc($districtResult)) {
    $districts[] = $row;
}
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
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
                            <h1 class="m-0 text-dark">View User</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">View User</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">View User</h3>
            <div class="card-tools">
                <a href="users.php" class="btn btn-primary"><i class="fas fa-eye"></i> User List</a> 
                <a href="edit-user.php?id=<?= $user['id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-12 ">
                    <h3>Basic Information</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <input type="text" class="form-control" 
                                    value="<?php 
                                        if ($user['gender'] == 'M') echo 'Male';
                                        elseif ($user['gender'] == 'F') echo 'Female';
                                        else echo 'Other'; 
                                    ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role</label>
                                <?php 
                                    $roleName = '';
                                    foreach ($roles as $role) {
                                        if ($role['id'] == $user['role']) {
                                            $roleName = $role['name'];
                                            break;
                                        }
                                    }
                                ?>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($roleName) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>GSTN</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['gstn']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pancard</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['pancard']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Aadhaar</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['aadhaar']) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <h3>Location</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>State</label>
                                <input type="text" class="form-control" value="Maharashtra" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>District</label>
                                <?php 
                                    $districtName = '';
                                    foreach ($districts as $district) {
                                        if ($district['id'] == $user['district_id']) {
                                            $districtName = $district['name'];
                                            break;
                                        }
                                    }
                                ?>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($districtName) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Taluka</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['taluka_id']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Village</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['village_id']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Address</label>
                                <textarea class="form-control" readonly><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <a href="users.php" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
