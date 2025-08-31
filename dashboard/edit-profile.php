<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch required dropdown values
$roles = $conn->query("SELECT id, name FROM roles")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT id, name FROM districts")->fetch_all(MYSQLI_ASSOC);
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

  <title>MBOCWCESS Portal | Edit Profile</title>
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
            <h1 class="m-0 text-dark">Edit Profile</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Profile</li>
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
                    <h3 class="card-title">Edit Profile</h3>
                    <div class="card-tools">
                        <!-- <a href="edit-profile.php" class="btn btn-primary" ><i class="fas fa-eye"></i> User List</a>  -->
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove"><i class="fas fa-times"></i></button>
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
                            <form action="update-profile.php" method="post" enctype="multipart/form-data">
                                <h3>Basic Information</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= $user['name'] ?>"  required>
                                            <input type="hidden" name="id" class="form-control" value="<?= $user['id'] ?>"  required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="tel" name="phone" class="form-control numeric" value="<?= $user['phone'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gender</label>
                                            <select name="gender" id="gender" class="form-control" required>
                                                <option value="">-- Select Gender --</option>
                                                <option value="M" <?= $user['gender'] == 'M' ? 'selected' : "" ?> >Male</option>
                                                <option value="F" <?= $user['gender'] == 'F' ? 'selected' : "" ?> >Female</option>
                                                <option value="O" <?= $user['gender'] == 'O' ? 'selected' : "" ?> >Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Role</label>
                                            <select name="role" id="role" class="form-control" disabled>
                                                <option value="">Choose role</option>
                                                <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['id'] ?>" <?= $user['role'] == $role['id'] ? 'selected' : "" ?> ><?= $role['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>GSTN</label>
                                            <input type="text" name="gstn" class="form-control" value="<?= $user['gstn'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pancard</label>
                                            <input type="text" name="pancard" class="form-control" value="<?= $user['pancard'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Aadhaar</label>
                                            <input type="text" name="aadhaar" class="form-control" value="<?= $user['aadhaar'] ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <h3>Location</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>State</label>
                                            <select name="state_id" id="state_id" class="form-control">
                                                <option value="">Choose State</option>
                                                <option value="14" selected>Maharashtra</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>District</label>
                                            <select name="district_id" id="district_id" class="form-control">
                                                <option value="">Choose District</option>
                                                <?php foreach ($districts as $district): ?>
                                                <option value="<?= $district['id'] ?>" <?= $user['district_id'] == $district['id'] ? 'selected' : "" ?> ><?= $district['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Taluka</label>
                                            <select name="taluka_id" id="taluka_id" class="form-control">
                                                <option value="" >Choose Taluka</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Village</label>
                                            <select name="village_id" id="village_id" class="form-control">
                                                <option value="">Choose Village</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <input type="text" name="address" class="form-control" value="<?= $user['address'] ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <br/><br/>
                                <button type="submit" class="btn btn-info">Submit</button>
                                <a href="dashboard.php" class="btn btn-default ml-2">Cancel</a>
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
<!-- AJAX for dynamic Project Type -->
<script>
    $('.numeric').on('input', function(event) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    // Get references to the dropdowns
    const districtSelect = document.getElementById('district_id');
    const talukaSelect = document.getElementById('taluka_id');
    const villageSelect = document.getElementById('village_id');

    // Function to fetch data from the server
    async function fetchData(url, bodyData) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: bodyData
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Fetch error:', error);
            return []; // Return empty array on error
        }
    }

    // Function to populate a dropdown
    function populateDropdown(selectElement, data, placeholderText) {
        // Clear existing options
        selectElement.innerHTML = `<option value="">${placeholderText}</option>`;
        // Add new options from the fetched data
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            selectElement.appendChild(option);
        });
    }

    // Event listener for the District dropdown
    districtSelect.addEventListener('change', async () => {
        const districtId = districtSelect.value;
        // Clear taluka and village dropdowns
        populateDropdown(talukaSelect, [], 'Choose Taluka');
        populateDropdown(villageSelect, [], 'Choose Village');

        if (districtId) {
            const talukas = await fetchData('fetch_data.php', `type=talukas&id=${districtId}`);
            populateDropdown(talukaSelect, talukas, 'Choose Taluka');
        }
    });

    // Event listener for the Taluka dropdown
    talukaSelect.addEventListener('change', async () => {
        const talukaId = talukaSelect.value;
        // Clear village dropdown
        populateDropdown(villageSelect, [], 'Choose Village');

        if (talukaId) {
            const villages = await fetchData('fetch_data.php', `type=villages&id=${talukaId}`);
            populateDropdown(villageSelect, villages, 'Choose Village');
        }
    });

</script>

</body>
</html>
