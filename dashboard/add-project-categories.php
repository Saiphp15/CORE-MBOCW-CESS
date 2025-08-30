<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $is_active = $_POST['is_active']; // dropdown value (1 or 0)

    if ($name == '') {
        $_SESSION['error'] = "Project Category Name is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO project_categories (name, description, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $is_active);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Project Category added successfully!";
            header("Location: project-categories.php");
            exit;
        } else {
            $_SESSION['error'] = "Error adding Project Category. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>MBOCWCESS Portal | Add Project Category</title>
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
    <!-- Content Header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Add Project Category</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Add Project Category</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Add New Project Category</h3>
            <div class="card-tools">
              <a href="project-categories.php" class="btn btn-primary"><i class="fas fa-eye"></i> View Categories</a>
            </div>
          </div>
          <div class="card-body p-4">
            <?php
              if (isset($_SESSION['success'])) {
                  echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
                  unset($_SESSION['success']);
              }
              if (isset($_SESSION['error'])) {
                  echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
                  unset($_SESSION['error']);
              }
            ?>
            <form action="" method="post">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="name">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter Category Name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" id="is_active" class="form-control">
                      <option value="1" selected>Active</option>
                      <option value="0">Inactive</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter Category Description"></textarea>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-success">Add Category</button>
              <a href="project-categories.php" class="btn btn-secondary ml-2">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <?php include('includes/footer.php'); ?>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

</body>
</html>
