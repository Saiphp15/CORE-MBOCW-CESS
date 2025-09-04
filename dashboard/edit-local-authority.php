<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// --- Get ID from query string ---
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['error'] = "Invalid authority ID.";
    header("Location: local-authorities.php");
    exit;
}
$authority_id = (int) $_GET['id'];

// --- Fetch current authority record ---
$stmt = $conn->prepare("
  SELECT la.*, lau.user_id 
  FROM local_authorities AS la 
  LEFT JOIN local_authorities_users AS lau 
    ON la.id = lau.local_authority_id 
  WHERE la.id = ?
");
$stmt->bind_param("i", $authority_id);
$stmt->execute();
$result = $stmt->get_result();
$authority = $result->fetch_assoc();
$stmt->close();

if (!$authority) {
    $_SESSION['error'] = "Authority not found.";
    header("Location: local-authorities.php");
    exit;
}

// --- Dropdown data ---
$local_authority_types = $conn->query("SELECT id, name FROM local_authority_types")->fetch_all(MYSQLI_ASSOC);
$authority_departments = $conn->query("SELECT id, name FROM authority_departments")->fetch_all(MYSQLI_ASSOC);
$local_authority_subdepartments = $conn->query("SELECT id, name FROM authority_subdepartments")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT id, name FROM districts")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, name FROM users WHERE role=3 AND is_active != 3")->fetch_all(MYSQLI_ASSOC);
$talukas = $conn->query("SELECT id, name FROM talukas WHERE district_id = " . (int)$authority['district_id'])->fetch_all(MYSQLI_ASSOC);
$villages = $conn->query("SELECT id, name FROM villages WHERE taluka_id = " . (int)$authority['taluka_id'])->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>MBOCWCESS Portal | Edit Local Authority</title>
  <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <?php include('includes/navbar.php'); ?>
  <?php include('includes/sidebar.php'); ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0 text-dark">Edit Local Authority</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="local-authorities.php">Home</a></li>
              <li class="breadcrumb-item active">Edit Local Authority</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Edit Implementing Agency</h3>
            <div class="card-tools">
              <a href="local-authorities.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Local Authority List
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
            <form action="update-local-authority.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?= htmlspecialchars($authority['id']) ?>">

              <h3>Basic Information</h3>
              <div class="row">
                <div class="col-md-6">
                  <label>Authority Name</label>
                  <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($authority['name']) ?>">
                </div>
                <div class="col-md-6">
                  <label>Authority Type</label>
                  <select name="authority_type_id" class="form-control">
                    <option value="">-- Select Type --</option>
                    <?php foreach ($local_authority_types as $type): ?>
                      <option value="<?= $type['id'] ?>" <?= ($authority['type_id'] == $type['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>Authority Department</label>
                  <select name="department_id" class="form-control">
                    <option value="">-- Select Department --</option>
                    <?php foreach ($authority_departments as $dept): ?>
                      <option value="<?= $dept['id'] ?>" <?= ($authority['authority_department_id'] == $dept['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>Sub Department</label>
                  <select name="subdepartment_id" class="form-control">
                    <option value="">-- Select Subdepartment --</option>
                    <?php foreach ($local_authority_subdepartments as $sub): ?>
                      <option value="<?= $sub['id'] ?>" <?= ($authority['authority_subdepartment_id'] == $sub['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sub['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <br>
              <h3>Authority Documents</h3>
              <div class="row">
                <div class="col-md-6">
                  <label>PAN Card Number</label>
                  <input type="text" class="form-control" name="pancard" maxlength="10" value="<?= htmlspecialchars($authority['pancard'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label>Upload PAN Card</label>
                  <input type="file" class="form-control-file" name="pancard_path" accept="image/*,application/pdf">
                  <?php if ($authority['pancard_path']): ?>
                    <small>Current: <a href="../<?= $authority['pancard_path'] ?>" target="_blank">View File</a></small>
                  <?php endif; ?>
                </div>
                <div class="col-md-6">
                  <label>Aadhaar Number</label>
                  <input type="text" class="form-control" name="aadhaar" maxlength="12" value="<?= htmlspecialchars($authority['aadhaar']) ?>">
                </div>
                <div class="col-md-6">
                  <label>Upload Aadhaar</label>
                  <input type="file" class="form-control-file" name="aadhaar_path" accept="image/*,application/pdf">
                  <?php if ($authority['aadhaar_path']): ?>
                    <small>Current: <a href="../<?= $authority['aadhaar_path'] ?>" target="_blank">View File</a></small>
                  <?php endif; ?>
                </div>
                <div class="col-md-6">
                  <label>GSTN</label>
                  <input type="text" class="form-control" name="gstn" maxlength="15" value="<?= htmlspecialchars($authority['gstn']) ?>">
                </div>
              </div>

              <br>
              <h3>Authority Location</h3>
              <div class="row">
                <div class="col-md-6">
                  <label>State</label>
                  <select name="state_id" class="form-control">
                    <option value="14" <?= ($authority['state_id'] == 14) ? 'selected' : '' ?>>Maharashtra</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>District</label>
                  <select name="district_id" class="form-control">
                    <option value="">-- Choose District --</option>
                    <?php foreach ($districts as $dist): ?>
                      <option value="<?= $dist['id'] ?>" <?= ($authority['district_id'] == $dist['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dist['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>Taluka</label>
                  <select name="taluka_id" class="form-control">
                    <option value="">-- Choose Taluka --</option>
                    <?php foreach ($talukas as $taluka): ?>
                      <option value="<?= $taluka['id'] ?>" <?= ($authority['taluka_id'] == $taluka['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($taluka['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label>Village</label>
                  <select name="village_id" class="form-control">
                    <option value="">-- Choose Village --</option>
                    <?php foreach ($villages as $village): ?>
                      <option value="<?= $village['id'] ?>" <?= ($authority['village_id'] == $village['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($village['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-12">
                  <label>Address</label>
                  <textarea name="address" class="form-control"><?= htmlspecialchars($authority['address'] ?? '') ?></textarea>
                </div>
              </div>

              <br>
              <h3>Authority User</h3>
              <div class="row">
                <div class="col-md-6">
                  <label>CAFO (Chief Account Finance Officer)</label>
                  <select name="user_id" class="form-control">
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $user): ?>
                      <option value="<?= $user['id'] ?>" <?= ($authority['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['name'] ?? '') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <br>
              <button type="submit" class="btn btn-info">Update</button>
              <a href="local-authorities.php" class="btn btn-default ml-2">Cancel</a>
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
