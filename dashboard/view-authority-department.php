<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Get category id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: project-categories-list.php"); // redirect to list
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM authority_departments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    $_SESSION['error'] = "Category not found!";
    header("Location: project-categories-list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MBOCWCESS Portal | View Project Category</title>
  <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <?php include('includes/navbar.php'); ?>
  <!-- /.navbar -->

  <!-- Sidebar -->
  <?php include('includes/sidebar.php'); ?>
  <!-- /.sidebar -->

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">View Project Category</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item"><a href="project-categories-list.php">Categories</a></li>
              <li class="breadcrumb-item active">View</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">View Project Category</h3>
            <div class="card-tools">
              <a href="project-categories.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Project Category List
              </a>
            </div>
          </div>
          <form>
            <div class="card-body">
              <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($category['name']); ?>" readonly>
              </div>
              <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" rows="3" readonly><?php echo htmlspecialchars($category['description']); ?></textarea>
              </div>
              <div class="form-group">
                <label for="status">Status</label>
                <input type="text" class="form-control" value="<?php echo ($category['is_active'] == 1 ? 'Active' : 'Inactive'); ?>" readonly>
              </div>
              <div class="form-group">
                <label for="created_at">Created At</label>
                <input type="text" class="form-control" value="<?php echo $category['created_at']; ?>" readonly>
              </div>
              <div class="form-group">
                <label for="updated_at">Updated At</label>
                <input type="text" class="form-control" value="<?php echo $category['updated_at']; ?>" readonly>
              </div>
            </div>
            <div class="card-footer">
              <a href="authority-departments.php" class="btn btn-secondary">Back</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Footer -->
  <?php include('includes/footer.php'); ?>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

</body>
</html>
