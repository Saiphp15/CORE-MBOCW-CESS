<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

if (empty($_GET['id'])) { $_SESSION['error']="Invalid request."; header("Location: project-types.php"); exit; }
$id = intval($_GET['id']);

$sql = "SELECT pt.*, pc.name AS category_name
        FROM authority_subdepartments pt
        LEFT JOIN authority_departments pc ON pc.id = pt.category_id
        WHERE pt.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) { $_SESSION['error']="Record not found."; header("Location: project-types.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Project Type</title>
  <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <?php include('includes/navbar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">View authority Type</h1>
        <div>
          <a href="project-types.php" class="btn btn-secondary"><i class="fas fa-list"></i> Back</a>
          <a href="edit-project-type.php?id=<?= $row['id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="card card-primary">
          <div class="card-header"><h3 class="card-title">Details</h3></div>
          <form>
            <div class="card-body">
              <div class="form-group">
                <label>Category</label>
                <input class="form-control" value="<?= htmlspecialchars($row['category_name'] ?? '—'); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Name</label>
                <input class="form-control" value="<?= htmlspecialchars($row['name']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($row['description']); ?></textarea>
              </div>
              <div class="form-group">
                <label>Status</label>
                <input class="form-control" value="<?= $row['is_active'] ? 'Active' : 'Inactive'; ?>" readonly>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Created At</label>
                  <input class="form-control" value="<?= $row['created_at']; ?>" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label>Updated At</label>
                  <input class="form-control" value="<?= $row['updated_at']; ?>" readonly>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
  <?php include('includes/footer.php'); ?>
</div>
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
</body>
</html>
