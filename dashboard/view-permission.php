<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';
require_once '../common/helper.php';
// Check if ID provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM permissions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Record not found");
}
$row = $result->fetch_assoc();
$stmt->close();

// Fetch required dropdown values
// $permissions = $conn->query("SELECT id, name FROM permissions")->fetch_all(MYSQLI_ASSOC);

// $result = $conn->query("SELECT permission_id FROM role_permissions WHERE role_id= $id")->fetch_all(MYSQLI_ASSOC);
// $rolePermissions = array_column($result, 'permission_id');

// print_r($rolePermissions);die;
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

  <title>MBOCWCESS Portal | Edit Local Authority</title>
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
            <h1 class="m-0 text-dark">View Permission</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">View Permission</li>
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
                    <h3 class="card-title">View Permission</h3>
                    <div class="card-tools">
                        <a href="permissions.php" class="btn btn-primary" ><i class="fas fa-eye"></i>Permission List</a> 
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
                            <form action="update-permission.php" method="post" enctype="multipart/form-data">
                                <!-- <h3>Role Information</h3> -->
                                <!-- <div class="row"> -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Permission Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" readonly>
                                            <input type="hidden" class="form-control" id="permission_id" name="permission_id" value="<?php echo $_GET['id']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="is_active" id="is_active" class="form-control" disabled>
                                                <option value="1" <?=  $row['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                                                <option value="2" <?=  $row['is_active']  == 2 ? 'selected' : '' ?> >Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                              
                                <br/><br/>
                                <a href="roles.php" class="btn btn-default ml-2">Cancel</a>
                                      
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
