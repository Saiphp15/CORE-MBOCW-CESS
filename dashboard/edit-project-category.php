<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Check if ID provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request");
}
$id = intval($_GET['id']);

// Fetch existing record
$stmt = $conn->prepare("SELECT * FROM project_categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Record not found");
}
$row = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MBOCWCESS Portal | Edit Project Category</title>
  <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <?php include('includes/navbar.php'); ?>
  <!-- Sidebar -->
  <?php include('includes/sidebar.php'); ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0 text-dark">Edit Project Category</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Project Category</li>
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
            <h3 class="card-title">Edit Project Category</h3>
            <div class="card-tools">
              <a href="project-categories.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Project Category List
              </a>
            </div>
          </div>

          <div class="card-body p-4">
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
            <form action="update-project-category.php" method="post">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

              <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" class="form-control" id="name" name="name"
                  value="<?php echo htmlspecialchars($row['name']); ?>" required>
              </div>

              <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>
              </div>

              <div class="form-group">
                <label for="is_active">Status</label>
                <select class="form-control" id="is_active" name="is_active">
                  <option value="1" <?php if ($row['is_active']==1) echo "selected"; ?>>Active</option>
                  <option value="0" <?php if ($row['is_active']==0) echo "selected"; ?>>Inactive</option>
                </select>
              </div>

              <button type="submit" class="btn btn-info">Update</button>
              <a href="project-categories.php" class="btn btn-default ml-2">Cancel</a>
            </form>
          </div>
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
