<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Get project ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid project ID.";
    header("Location: projects.php");
    exit;
}
$project_id = intval($_GET['id']);

// Fetch project data
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

// Redirect if project not found
if (!$project) {
    $_SESSION['error'] = "Project not found.";
    header("Location: projects.php");
    exit;
}

// Fetch names for dropdown values for display with null checks
$category_name = 'N/A';
if (!empty($project['project_category_id'])) {
    $category_name = $conn->query("SELECT name FROM authority_departments WHERE id = " . $project['project_category_id'])->fetch_assoc()['name'] ?? 'N/A';
}

$project_type_name = 'N/A';
if (!empty($project['project_type_id'])) {
    $project_type_name = $conn->query("SELECT name FROM authority_subdepartments WHERE id = " . $project['project_type_id'])->fetch_assoc()['name'] ?? 'N/A';
}

$local_authority_name = 'N/A';
if (!empty($project['local_authority_id'])) {
    $local_authority_name = $conn->query("SELECT name FROM local_authorities WHERE id = " . $project['local_authority_id'])->fetch_assoc()['name'] ?? 'N/A';
}

$district_name = 'N/A';
if (!empty($project['district_id'])) {
    $district_name = $conn->query("SELECT name FROM districts WHERE id = " . $project['district_id'])->fetch_assoc()['name'] ?? 'N/A';
}

$taluka_name = 'N/A';
if (!empty($project['taluka_id'])) {
    $taluka_name = $conn->query("SELECT name FROM talukas WHERE id = " . $project['taluka_id'])->fetch_assoc()['name'] ?? 'N/A';
}

$village_name = 'N/A';
if (!empty($project['village_id'])) {
    $village_name = $conn->query("SELECT name FROM villages WHERE id = " . $project['village_id'])->fetch_assoc()['name'] ?? 'N/A';
}

// Fetch work orders using a prepared statement (Security)
$stmt_wo = $conn->prepare("SELECT * FROM project_work_orders WHERE project_id = ?");
$stmt_wo->bind_param("i", $project_id);
$stmt_wo->execute();
$work_orders = $stmt_wo->get_result()->fetch_all(MYSQLI_ASSOC);


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

  <title>Medical POS System Desk | View Project Details</title>
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
            <h1 class="m-0 text-dark">View Project Details</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">View Project Details</li>
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
                    <h3 class="card-title">View Project Details</h3>
                    <div class="card-tools">
                        <a href="projects.php" class="btn btn-primary" ><i class="fas fa-eye"></i> Project List</a> 
                        <a href="edit-project.php?id=<?= $project['id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Project</a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <h3>Basic Project Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Project Name:</strong> <?= htmlspecialchars($project['project_name']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project Category:</strong> <?= htmlspecialchars($category_name) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project Type:</strong> <?= htmlspecialchars($project_type_name) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Local Authority:</strong> <?= htmlspecialchars($local_authority_name) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Construction Cost:</strong> ₹<?= number_format($project['construction_cost'], 2) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project Start Date:</strong> <?= htmlspecialchars($project['project_start_date']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project End Date:</strong> <?= htmlspecialchars($project['project_end_date']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Cess Amount:</strong> ₹<?= number_format($project['cess_amount'], 2) ?></p>
                                </div>
                            </div>

                            <h3>Project Location</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>State:</strong> Maharashtra</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>District:</strong> <?= htmlspecialchars($district_name) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Taluka:</strong> <?= htmlspecialchars($taluka_name) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Village:</strong> <?= htmlspecialchars($village_name) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Pin Code:</strong> <?= htmlspecialchars($project['pin_code'] ?? '') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project Address:</strong> <?= htmlspecialchars($project['project_address'] ?? '') ?></p>
                                </div>
                            </div>

                            <h3>Work Order Details</h3>
                            <?php if (!empty($work_orders)): ?>
                                <?php foreach ($work_orders as $index => $wo):
                                    // Fetch names for work order details
                                    // Initialize names to 'N/A'
                                    $wo_manager_name = 'N/A';
                                    $wo_engineer_name = 'N/A';
                                    $wo_employer_name = 'N/A';

                                    // Fetch names for work order details with null checks
                                    if (!empty($wo['work_order_manager_id'])) {
                                        $wo_manager_name = $conn->query("SELECT name FROM users WHERE id = " . $wo['work_order_manager_id'])->fetch_assoc()['name'] ?? 'N/A';
                                    }
                                    
                                    if (!empty($wo['work_order_engineer_id'])) {
                                        $wo_engineer_name = $conn->query("SELECT name FROM users WHERE id = " . $wo['work_order_engineer_id'])->fetch_assoc()['name'] ?? 'N/A';
                                    }

                                    if (!empty($wo['work_order_employer_id'])) {
                                        $wo_employer_name = $conn->query("SELECT name FROM employers WHERE id = " . $wo['work_order_employer_id'])->fetch_assoc()['name'] ?? 'N/A';
                                    }
                                ?>
                                <div class="work-order-section border p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Work Order Number:</strong> <?= htmlspecialchars($wo['work_order_number']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Work Order Date:</strong> <?= htmlspecialchars($wo['work_order_date']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Work Order Amount:</strong> ₹<?= number_format($wo['work_order_amount'], 2) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Approval Letter:</strong>
                                            <?php if ($wo['work_order_approval_letter']): ?>
                                                <a href="../uploads/work_orders/<?= htmlspecialchars($wo['work_order_approval_letter']) ?>" target="_blank">View File</a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Manager:</strong> <?= htmlspecialchars($wo_manager_name) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Engineer:</strong> <?= htmlspecialchars($wo_engineer_name) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Employer:</strong> <?= htmlspecialchars($wo_employer_name) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No work orders found for this project.</p>
                            <?php endif; ?>

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
