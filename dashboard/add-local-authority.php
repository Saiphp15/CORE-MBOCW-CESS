<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Fetch required dropdown values
$local_authority_types = $conn->query("SELECT id, name FROM local_authority_types")->fetch_all(MYSQLI_ASSOC);
$authority_departments = $conn->query("SELECT id, name FROM authority_departments")->fetch_all(MYSQLI_ASSOC);
$local_authority_subdepartments = $conn->query("SELECT id, name FROM authority_subdepartments")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT id, name FROM districts")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, name FROM users where role=3 AND is_active != 3")->fetch_all(MYSQLI_ASSOC);
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

  <title>MBOCWCESS Portal | Add Implementing Agency</title>
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
            <h1 class="m-0 text-dark">Add New Local Authority</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Add New Local Authority</li>
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
                    <h3 class="card-title">Add Implementing Agency</h3>
                    <div class="card-tools">
                        <a href="local-authorities.php" class="btn btn-primary" ><i class="fas fa-eye"></i>Local Authority List</a>
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
                            <form action="save-local-authority.php" method="post" enctype="multipart/form-data">
                                <h3>Basic Information</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Authority Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_SESSION['old_values']['name']) ? htmlspecialchars($_SESSION['old_values']['name']) : ''; ?>" >
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Authority Type</label>
                                            <select name="authority_type_id" id="authority_type_id" class="form-control" >
                                                <option value="">-- Select Authority Type --</option>
                                                <?php foreach ($local_authority_types as $authority_type): ?>
                                                    <option value="<?= $authority_type['id'] ?>" 
                                                        <?= (isset($_SESSION['old_values']['authority_type_id']) 
                                                            && $_SESSION['old_values']['authority_type_id'] == $authority_type['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($authority_type['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Authority Department</label>
                                            <select name="department_id" id="department_id" class="form-control" >
                                                <option value="">-- Select Department --</option>
                                                <?php foreach ($authority_departments as $department): ?>
                                                    <option value="<?= $department['id'] ?>" 
                                                        <?= (isset($_SESSION['old_values']['department_id']) 
                                                            && $_SESSION['old_values']['department_id'] == $department['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($department['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Authority Sub Department</label>
                                            <select name="subdepartment_id" id="subdepartment_id" class="form-control" >
                                                <option value="">-- Select Subdepartment --</option>
                                                <?php foreach ($local_authority_subdepartments as $subdept): ?>
                                                    <option value="<?= $subdept['id'] ?>" 
                                                        <?= (isset($_SESSION['old_values']['subdepartment_id']) 
                                                            && $_SESSION['old_values']['subdepartment_id'] == $subdept['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($subdept['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <h3>Authority Documents</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pancard">PAN Card Number</label>
                                            <input type="text" class="form-control" id="pancard" name="pancard" 
                                                value="<?= isset($_SESSION['old_values']['pancard']) ? htmlspecialchars($_SESSION['old_values']['pancard']) : '' ?>" 
                                                maxlength="10" placeholder="Enter PAN Number (e.g. ABCDE1234F)">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pancard_path">Upload PAN Card</label>
                                            <input type="file" class="form-control-file" id="pancard_path" name="pancard_path" accept="image/*,application/pdf">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aadhaar">Aadhaar Number</label>
                                            <input type="text" class="form-control" id="aadhaar" name="aadhaar" 
                                                value="<?= isset($_SESSION['old_values']['aadhaar']) ? htmlspecialchars($_SESSION['old_values']['aadhaar']) : '' ?>" 
                                                maxlength="12" placeholder="Enter Aadhaar Number">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aadhaar_path">Upload Aadhaar Card</label>
                                            <input type="file" class="form-control-file" id="aadhaar_path" name="aadhaar_path" accept="image/*,application/pdf">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gstn">GSTN</label>
                                            <input type="text" class="form-control" id="gstn" name="gstn" 
                                                value="<?= isset($_SESSION['old_values']['gstn']) ? htmlspecialchars($_SESSION['old_values']['gstn']) : '' ?>" 
                                                maxlength="15" placeholder="Enter GST Number">
                                        </div>
                                    </div>
                                </div>

                                <h3>Authority Location</h3>
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
                                                    <option value="<?= $district['id'] ?>" 
                                                        <?= (isset($_SESSION['old_values']['district_id']) 
                                                            && $_SESSION['old_values']['district_id'] == $district['id']) ? 'selected' : '' ?>>
                                                        <?= $district['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Taluka</label>
                                            <select name="taluka_id" id="taluka_id" class="form-control">
                                                <option value="">Choose Taluka</option>
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
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea name="address" class="form-control" placeholder="Enter Authority Address"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <h3>Authority User</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Authority CAFO(Cheif Account Finance Officer)</label>
                                            <select name="user_id" id="user_id" class="form-control" >
                                                <option value="">-- Select User --</option>
                                                <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" 
                                                        <?= (isset($_SESSION['old_values']['user_id']) 
                                                            && $_SESSION['old_values']['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <br/><br/>
                                <button type="submit" class="btn btn-info">Submit</button>
                                <a href="local-authorities.php" class="btn btn-default ml-2">Cancel</a>
                                      
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

    $('#department_id').on('change', function () {
        const categoryId = $(this).val();
        if (categoryId) {
            $.get('get-types.php?department_id=' + categoryId, function (data) {
            $('#subdepartment_id').html(data);
            });
        } else {
            $('#subdepartment_id').html('<option value="">-- Select Subdepartment --</option>');
        }
    });

</script>

</body>
</html>
