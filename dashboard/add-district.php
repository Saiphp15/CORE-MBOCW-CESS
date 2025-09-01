<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $state_id = intval($_POST['state_id']);
    $status = intval($_POST['active_status']);

    $query = "INSERT INTO districts (name, state_id, active_status, created_by) 
              VALUES ('$name', $state_id, $status, 1)";
    if (mysqli_query($conn, $query)) {
        header("Location: districts.php?msg=District Added Successfully");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
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
                        <div class="card-header">
                            <h3 class="card-title">Add User</h3>
                            <div class="card-tools">
                                <a href="users.php" class="btn btn-primary"><i class="fas fa-eye"></i> User List</a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                    data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove"
                                    data-toggle="tooltip" title="Remove"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-12 ">
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
                                    <form method="post">
                                        <div class="form-group">
                                            <label>District Name</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>State</label>
                                            <select name="state_id" class="form-control" required>
                                                <option value="21">Maharashtra</option>
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
                                        <a href="districts.php" class="btn btn-secondary">Back</a>
                                    </form>
                                    <!-- /.card-body -->
                                </div>
                            </div><!-- /.container-fluid -->
                        </div>
                        <!-- /.content -->
                    </div>
                    <!-- /.content-wrapper -->

                    <!-- Main Footer -->
                    <?php include('includes/footer.php'); ?>
                </div>
                <!-- ./wrapper -->

                <!-- REQUIRED SCRIPTS -->

                <!-- jQuery -->
                <script src="../plugins/jquery/jquery.min.js"></script>
                <!-- Bootstrap 4 -->
                <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
                <!-- AdminLTE App -->
                <script src="../dist/js/adminlte.min.js"></script>
                <!-- AJAX for dynamic Project Type -->
               

</body>

</html>