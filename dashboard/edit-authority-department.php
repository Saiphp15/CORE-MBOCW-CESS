<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: authority-departments.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch department details
$stmt = $conn->prepare("SELECT id, name, description FROM authority_departments WHERE id = ? AND is_active != 3 LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Authority Department not found.";
    header("Location: authority-departments.php");
    exit;
}

$department = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>MBOCWCESS Portal | Edit Authority Department</title>
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

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Edit Authority Department</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Edit Authority Department</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Edit Authority Department</h3>
            <div class="card-tools">
              <a href="authority-departments.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Authority Department List
              </a>
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
                <form action="update-authority-department.php" method="post">
                  <input type="hidden" name="id" value="<?php echo htmlspecialchars($department['id']); ?>">

                  <h3>Basic Information</h3>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="name" class="form-label">Authority Department Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($department['name'] ?? ''); ?>" >
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($department['description'] ?? ''); ?></textarea>
                      </div>
                    </div>
                  </div>

                  <br/>
                  <button type="submit" class="btn btn-info">Update</button>
                  <a href="authority-departments.php" class="btn btn-default ml-2">Cancel</a>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Footer -->
  <?php include('includes/footer.php'); ?>
</div>

<!-- REQUIRED SCRIPTS -->
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
</body>
</html>
