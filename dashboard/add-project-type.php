<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once '../config/db.php';

// Load active categories for dropdown
$categories = $conn->query("SELECT id, name FROM project_categories WHERE is_active=1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle POST
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $category_id      = intval($_POST['category_id'] ?? 0);
  $name             = trim($_POST['name'] ?? '');
  $description      = trim($_POST['description'] ?? '');
 // $cess_trigger     = trim($_POST['cess_trigger'] ?? '');
//  $how_cess_is_paid = trim($_POST['how_cess_is_paid'] ?? '');
  $is_active        = intval($_POST['is_active'] ?? 1);

  if ($category_id<=0 || $name==='') {
    $_SESSION['error'] = "Category and Name are required.";
  } else {
    $stmt = $conn->prepare("INSERT INTO project_types (category_id, name, description, is_active) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $category_id, $name, $description, $is_active);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Project Type added successfully.";
      header("Location: project-types.php"); exit;
    } else {
      $_SESSION['error'] = "Insert failed: " . $conn->error;
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
  <title>MBOCWCESS Portal | Add Project Type</title>
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
        <h1 class="m-0">Add Project Type</h1>
        <a href="project-types.php" class="btn btn-secondary"><i class="fas fa-list"></i> Back to List</a>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="card card-primary">
          <div class="card-header"><h3 class="card-title">Basic Information</h3></div>
          <form method="post">
            <div class="card-body">
              <?php
                if (!empty($_SESSION['success'])) { echo "<div class='alert alert-success'>".$_SESSION['success']."</div>"; unset($_SESSION['success']); }
                if (!empty($_SESSION['error']))   { echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>"; unset($_SESSION['error']); }
              ?>

              <div class="form-group">
                <label>Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-control" required>
                  <option value="">-- Select Category --</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input name="name" class="form-control" placeholder="Enter project type name" required>
              </div>

              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Optional"></textarea>
              </div>

              <!-- <div class="form-group">
                <label>Cess Trigger</label>
                <textarea name="cess_trigger" class="form-control" rows="3" placeholder="e.g., when total cost exceeds X"></textarea>
              </div>

              <div class="form-group">
                <label>How Cess Is Paid</label>
                <textarea name="how_cess_is_paid" class="form-control" rows="3" placeholder="e.g., paid monthly/quarterly via portal"></textarea>
              </div> -->

              <div class="form-group">
                <label>Status</label>
                <select name="is_active" class="form-control">
                  <option value="1" selected>Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>

            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-success">Save</button>
              <a href="project-types.php" class="btn btn-secondary ml-2">Cancel</a>
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
