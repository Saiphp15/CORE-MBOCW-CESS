<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Get user id from query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid User ID.";
    header("Location: users.php");
    exit;
}

$user_id = intval($_GET['id']);

// Fetch user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "User not found.";
    header("Location: users.php");
    exit;
}
$user = mysqli_fetch_assoc($result);

// Fetch roles
$roles = [];
$r_sql = "SELECT * FROM roles";
$r_res = mysqli_query($conn, $r_sql);
while ($row = mysqli_fetch_assoc($r_res)) {
    $roles[] = $row;
}

// Fetch districts
$districts = [];
$d_sql = "SELECT * FROM districts WHERE state_id = 14";
$d_res = mysqli_query($conn, $d_sql);
while ($row = mysqli_fetch_assoc($d_res)) {
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
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit User</h3>
                            <div class="card-tools">
                                <a href="users.php" class="btn btn-primary"><i class="fas fa-eye"></i> User List</a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                    if (isset($_SESSION['success'])) {
                                        echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
                                        unset($_SESSION['success']);
                                    }
                                    if (isset($_SESSION['error'])) {
                                        echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                                        unset($_SESSION['error']);
                                    }
                                    ?>
                                    <form action="update-user.php" method="post">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">

                                        <h3>Basic Information</h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Full Name</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="<?= $user['name'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="<?= $user['email'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Contact Number</label>
                                                    <input type="tel" name="phone" class="form-control"
                                                        value="<?= $user['phone'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Gender</label>
                                                    <select name="gender" class="form-control" required>
                                                        <option value="">-- Select Gender --</option>
                                                        <option value="M" <?= $user['gender'] == 'M' ? 'selected' : '' ?>>
                                                            Male
                                                        </option>
                                                        <option value="F" <?= $user['gender'] == 'F' ? 'selected' : '' ?>>
                                                            Female</option>
                                                        <option value="O" <?= $user['gender'] == 'O' ? 'selected' : '' ?>>
                                                            Other
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Role</label>
                                                    <select name="role" class="form-control">
                                                        <option value="">Choose role</option>
                                                        <?php foreach ($roles as $role): ?>
                                                            <option value="<?= $role['id'] ?>" <?= $user['role'] == $role['id'] ? 'selected' : '' ?>>
                                                                <?= $role['name'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>GSTN</label>
                                                    <input type="text" name="gstn" class="form-control"
                                                        value="<?= $user['gstn'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Pancard</label>
                                                    <input type="text" name="pancard" class="form-control"
                                                        value="<?= $user['pancard'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Aadhaar</label>
                                                    <input type="text" name="aadhaar" class="form-control"
                                                        value="<?= $user['aadhaar'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <h3>Location</h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>State</label>
                                                    <select name="state_id" class="form-control">
                                                        <option value="14" <?= $user['state_id'] == 14 ? 'selected' : '' ?>>
                                                            Maharashtra</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>District</label>
                                                    <select name="district_id" class="form-control">
                                                        <option value="">Choose District</option>
                                                        <?php foreach ($districts as $district): ?>
                                                            <option value="<?= $district['id'] ?>"
                                                                <?= $user['district_id'] == $district['id'] ? 'selected' : '' ?>>
                                                                <?= $district['name'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Taluka</label>
                                                    <select name="taluka_id" class="form-control">
                                                        <option value="<?= $user['taluka_id'] ?>">Selected Taluka
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Village</label>
                                                    <select name="village_id" class="form-control">
                                                        <option value="<?= $user['village_id'] ?>">Selected Village
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Address</label>
                                                    <input type="text" name="address" class="form-control"
                                                        value="<?= $user['address'] ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <br><br>
                                        <button type="submit" class="btn btn-info">Update</button>
                                        <a href="users.php" class="btn btn-default ml-2">Cancel</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>