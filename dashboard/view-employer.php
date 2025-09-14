<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
// Assume you have a database connection file
require_once '../config/db.php';

$old_data = $_SESSION['old_data'] ?? [];
$employer = [];
$employer_id = $_GET['id'] ?? null;

// Fetch employer data from the database
if ($employer_id) {
    $sql = "SELECT * FROM `employers` WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employer = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Employer not found.";
        header("Location: employers.php");
        exit;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "No employer ID provided.";
    header("Location: employers.php");
    exit;
}

$conn->close();

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

  <title>MBOCWCESS Portal | Edit Employer</title>
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
            <h1 class="m-0 text-dark">Edit Employer</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Employer</li>
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
                    <h3 class="card-title">Edit Employer</h3>
                    <div class="card-tools">
                        <a href="employers.php" class="btn btn-primary" ><i class="fas fa-eye"></i> Employer List</a> 
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
                            <form action="update-employer.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($employer['id'] ?? ''); ?>">
                                <h3>Basic Information</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($old_data['name'] ?? $employer['name'] ?? ''); ?>" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employer_type">Employer Type</label>
                                            <select name="employer_type" id="employer_type" class="form-control" disabled>
                                                <option value="">Select Employer Type</option>
                                                <option value="Individual" <?php echo (($employer['employer_type'] ?? '') == 'Individual') ? 'selected' : ''; ?>>Individual</option>
                                                <option value="Private Company" <?php echo (($employer['employer_type'] ?? '') == 'Private Company') ? 'selected' : ''; ?>>Private Company</option>
                                                <option value="Government Organization" <?php echo (($employer['employer_type'] ?? '') == 'Government Organization') ? 'selected' : ''; ?>>Government Organization</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($employer['email'] ?? ''); ?>" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($employer['phone'] ?? ''); ?>" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Pancard NO</label>
                                                    <input type="text" name="pancard" value="<?php echo htmlspecialchars($employer['pancard'] ?? ''); ?>" class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Pancard Copy</label>
                                                    <?php if (!empty($employer['pancard_path'])): ?>
                                                        <a href="<?php echo htmlspecialchars($employer['pancard_path']); ?>" target="_blank">View Current PAN Card</a>
                                                    <?php endif; ?>
                                                    <input type="file" name="pancard_path" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Aadhaar</label>
                                                    <input type="text" name="aadhaar" value="<?php echo htmlspecialchars($employer['aadhaar'] ?? ''); ?>" class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Aadhaar Copy</label>
                                                    <?php if (!empty($employer['aadhaar_path'])): ?>
                                                        <a href="<?php echo htmlspecialchars($employer['aadhaar_path']); ?>" target="_blank">View Current Aadhaar Card</a>
                                                    <?php endif; ?>
                                                    <input type="file" name="aadhaar_path" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>GSTN</label>
                                            <input type="text" name="gstn" value="<?php echo htmlspecialchars($employer['gstn'] ?? ''); ?>" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <br/><br/>
                                <!-- <button type="submit" class="btn btn-info">Submit</button> -->
                                <a href="employers.php" class="btn btn-default ml-2">Cancel</a>
                                      
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
</body>
</html>
