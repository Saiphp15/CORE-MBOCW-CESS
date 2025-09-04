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
    header("Location: authority-subdepartments.php");
    exit;
}

$id = (int)$_GET['id'];

// === Fetch active departments ===
$stmt = $conn->prepare("SELECT id, name FROM authority_departments WHERE is_active=1 ORDER BY name");
$stmt->execute();
$departments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// === Fetch existing subdepartment record ===
$stmt = $conn->prepare("SELECT * FROM authority_subdepartments WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();
if (!$row) {
    $_SESSION['error'] = "Authority Sub Department not found.";
    header("Location: authority-subdepartments.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>MBOCWCESS Portal | Edit Authority Sub Department</title>
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
            <h1 class="m-0 text-dark">Edit Authority Sub Department</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Edit Authority Sub Department</li>
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
            <h3 class="card-title">Edit Authority Sub Department</h3>
            <div class="card-tools">
              <a href="authority-departments.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Authority Sub Department List
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
                <form action="update-authority-subdepartment.php" method="post">
                  <input type="hidden" name="id" value="<?= $row['id']; ?>">
                  <div class="card-body">
                    <?php
                      if (!empty($_SESSION['success'])) {
                          echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
                          unset($_SESSION['success']);
                      }
                      if (!empty($_SESSION['error'])) {
                          echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
                          unset($_SESSION['error']);
                      }
                    ?>

                    <div class="form-group">
                      <label>Authority Department <span class="text-danger">*</span></label>
                      <select name="department_id" class="form-control" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $d): ?>
                          <option value="<?= $d['id']; ?>" <?= ($d['id']==$row['department_id'])?'selected':''; ?>>
                            <?= htmlspecialchars($d['name']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Sub Department Name <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control" 
                            value="<?= htmlspecialchars($row['name']); ?>" required>
                    </div>

                    <div class="form-group">
                      <label>Description</label>
                      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($row['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                      <label>Status</label>
                      <select name="is_active" class="form-control">
                        <option value="1" <?= ($row['is_active']==1) ? 'selected':''; ?>>Active</option>
                        <option value="2" <?= ($row['is_active']==2) ? 'selected':''; ?>>Inactive</option>
                      </select>
                    </div>

                  </div>
                  <div class="card-footer">
                    <button type="submit" class="btn btn-info">Update</button>
                    <a href="authority-subdepartments.php" class="btn btn-default ml-2">Cancel</a>
                  </div>
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
