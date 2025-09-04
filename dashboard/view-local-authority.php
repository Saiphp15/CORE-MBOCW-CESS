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
$stmt = $conn->prepare("SELECT * FROM local_authorities WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Record not found");
}
$row = $result->fetch_assoc();
$stmt->close();

// Fetch required dropdown values
$local_authority_types = $conn->query("SELECT id, name FROM local_authority_types")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT id, name FROM districts")->fetch_all(MYSQLI_ASSOC);
$departments = $conn->query("SELECT id, name FROM authority_departments")->fetch_all(MYSQLI_ASSOC);
$authoritySubdepartments = $conn->query("SELECT id, name FROM authority_subdepartments")->fetch_all(MYSQLI_ASSOC);

// Fetch talukas and villages based on existing selection
$talukas = [];
$villages = [];
if( $row['district_id'] ) {
    $district_id = isset($row['district_id']) ? $row['district_id'] : 0;
    $talukas = $conn->query("SELECT id, name FROM talukas WHERE district_id=$district_id")->fetch_all(MYSQLI_ASSOC);
}
if( $row['taluka_id'] ) {
    $taluka_id = isset($row['taluka_id']) ? $row['taluka_id'] : 0;
    $villages = $conn->query("SELECT id, name FROM villages WHERE taluka_id=$taluka_id")->fetch_all(MYSQLI_ASSOC);
}
$village_id = isset($row['village_id']) ? $row['village_id'] : 0;
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

  <title>MBOCWCESS Portal | View Implementing Angency</title>
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
            <h1 class="m-0 text-dark">View Implementing Agency</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">View Implementing Agency</li>
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
                    <h3 class="card-title">View Implementing Agency</h3>
                    <div class="card-tools">
                        <a href="local-authorities.php" class="btn btn-primary" ><i class="fas fa-eye"></i>Implementing Agency List</a> 
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-12 ">
                            
                            <h3>Basic Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($row['name'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="type_id" id="type_id" class="form-control" readonly>
                                            <option value="">-- Select Type --</option>
                                            <?php foreach ($local_authority_types as $authority_type): ?>
                                                <option value="<?= $authority_type['id'] ?>" <?= ($authority_type['id'] == $row['type_id']) ? 'selected' : '' ?>><?= htmlspecialchars($authority_type['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="authority_department_id" id="authority_department_id" class="form-control"  readonly>
                                            <option value="">-- Select Department --</option>
                                            <?php foreach ($departments as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= $cat['id']==$row['authority_department_id']?'selected':'' ?>><?= $cat['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sub Department</label>
                                        <select name="authority_subdepartment_id" id="authority_subdepartment_id" class="form-control" readonly>
                                            <option value="">-- Select Sub Department --</option>
                                            <?php foreach ($authoritySubdepartments as $subdep): ?>
                                                <option value="<?= $subdep['id'] ?>" <?= $subdep['id']==$row['authority_subdepartment_id']?'selected':'' ?>><?= $type['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <h3>Authority Location</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        <select name="state_id" id="state_id" class="form-control" readonly>
                                            <option value="">Choose State</option>
                                            <option value="14" selected>Maharashtra</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>District</label>
                                        <select name="district_id" id="district_id" class="form-control" readonly>
                                            <option value="">Choose District</option>
                                            <?php foreach ($districts as $district): ?>
                                                <option value="<?= $district['id'] ?>" <?= ($district['id'] == $district_id) ? 'selected' : '' ?>><?= htmlspecialchars($district['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Taluka</label>
                                        <select name="taluka_id" id="taluka_id" class="form-control" readonly>
                                            <option value="">Choose Taluka</option>
                                            <?php foreach ($talukas as $taluka): ?>
                                                <option value="<?= $taluka['id'] ?>" <?= ($taluka['id'] == $taluka_id) ? 'selected' : '' ?>><?= htmlspecialchars($taluka['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Village</label>
                                        <select name="village_id" id="village_id" class="form-control" readonly>
                                            <option value="">Choose Village</option>
                                            <?php foreach ($villages as $village): ?>
                                                <option value="<?= $village['id'] ?>" <?= ($village['id'] == $village_id) ? 'selected' : '' ?>><?= htmlspecialchars($village['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control" placeholder="Enter Authority Address" readonly>
                                            <?= htmlspecialchars($row['address'] ?? '') ?>
                                        </textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <br/><br/>
                            <a href="local-authorities.php" class="btn btn-default ml-2">Back</a>
                                   
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
