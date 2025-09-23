<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit;
}
require_once '../config/db.php';

// check workorder and project ID
$project_id = intval($_GET['project_id']);
$workorder_id = intval($_GET['workorder_id']);
if (!isset($_GET['workorder_id']) || empty($_GET['workorder_id'])) {
  $_SESSION['error'] = "Invalid Workorder ID.";
  header("Location: view-project.php?id=".$project_id); exit;
}

if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
  $_SESSION['error'] = "Invalid Project ID.";
  header(header: "Location: view-project.php?id=".$project_id); exit;
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

  <title>MMBOCWCESS Portal | Raise Invoice</title>
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
              <h1 class="m-0 text-dark">Raise Invoice</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Raise Invoice</li>
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
              <h3>Raise Invoice</h3>
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
                  <form action="save-raise-invoice.php" method="post" enctype="multipart/form-data">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="name" class="form-label">Amount</label>
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        <input type="hidden" name="workorder_id" value="<?= $workorder_id ?>">
                        <input type="text" class="form-control numeric" id="amount" name="amount" placeholder="Enter Invoice Amount" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type" id="payment_type" class="form-control" required>
                          <option value="netbanking">Netbanking</option>
                          <option value="challan">Challan</option>
                        </select>
                      </div>
                    </div>
                    <br /><br />
<<<<<<< HEAD:dashboard/raise-workorder-invoice.php
                    <button type="submit" class="btn btn-success">Submit</button>
                    <a href="view-project.php?id=<?= $_GET['project_id']; ?>" class="btn btn-default ml-2">Cancel</a>
=======
                    <button type="submit" class="btn btn-info">Save</button>
                    <a href="projects.php" class="btn btn-default ml-2">Cancel</a>
>>>>>>> 1b34e11c81b97c8f4d08d80fb7589777799bfc87:dashboard/raise-invoice.php
                  </form>
                </div>
              </div>
            </div>
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
  <script type="text/javascript">
    $('.numeric').on('input', function(event) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
  </script>
</body>

</html>