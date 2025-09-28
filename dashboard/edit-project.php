<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php';

// Get project ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid project ID.";
    header("Location: projects.php");
    exit;
}
$project_id = intval($_GET['id']);

// Fetch project data
$stmt = $conn->prepare("
SELECT p.*, lau.local_authority_id 
FROM projects p
LEFT JOIN local_authorities_users lau ON p.local_authority_id = lau.local_authority_id
WHERE lau.user_id =? AND lau.is_active = 1 AND p.id = ? 
");
$loggedInUserId = intval($_SESSION['user_id']);
$stmt->bind_param("ii", $loggedInUserId, $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

// Fetch required dropdown values
$users = $conn->query("SELECT id, name FROM users")->fetch_all(MYSQLI_ASSOC);
$employers = $conn->query("SELECT id, name FROM employers")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT id, name FROM districts")->fetch_all(MYSQLI_ASSOC);
$talukas = $conn->query("SELECT id, name FROM talukas")->fetch_all(MYSQLI_ASSOC);
$villages = $conn->query("SELECT id, name FROM villages")->fetch_all(MYSQLI_ASSOC);

// Fetch work orders
$work_orders = $conn->query("SELECT * FROM project_work_orders WHERE project_id = $project_id")->fetch_all(MYSQLI_ASSOC);
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

  <title>MBOCWCESS Portal | Edit Project</title>
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
            <h1 class="m-0 text-dark">Edit Project</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Edit Project</li>
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
                    <h3 class="card-title">Edit Project</h3>
                    <div class="card-tools">
                        <a href="projects.php" class="btn btn-primary" ><i class="fas fa-eye"></i> Project List</a> 
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
                            <form action="update-project.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                <input type="hidden" name="local_authority_id" value="<?= $project['local_authority_id'] ?>">
                                <!-- 
                                    Note: The following hidden input is added to ensure that the local authority ID
                                    associated with the logged-in user is passed along with the form submission.
                                    This is important for maintaining data integrity and ensuring that the project
                                    is correctly linked to the appropriate local authority.
                                -->
                                 
                                <h3>Basic Project Information</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Project Name</label>
                                            <input type="text" name="project_name" class="form-control" value="<?= htmlspecialchars($project['project_name']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Construction Cost (₹)</label>
                                            <input type="number" value="<?= $project['construction_cost'] ?>" name="construction_cost" id="construction_cost" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Project Start Date</label>
                                            <input type="date" value="<?= $project['project_start_date'] ?>" name="project_start_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Project End Date</label>
                                            <input type="date" value="<?= $project['project_end_date'] ?>" name="project_end_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cess Amount</label>
                                            <input type="number" value="<?= $project['cess_amount'] ?>" name="cess_amount" id="cess_amount" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                <h3>Project Location</h3>
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
                                                <option value="<?= $district['id'] ?>" <?= $district['id']==$project['district_id']?'selected':'' ?>><?= $district['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Taluka</label>
                                            <select name="taluka_id" id="taluka_id" class="form-control">
                                                <option value="">Choose Taluka</option>
                                                <?php foreach ($talukas as $taluka): ?>
                                                <option value="<?= $taluka['id'] ?>" <?= $taluka['id']==$project['taluka_id']?'selected':'' ?>><?= $taluka['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Village</label>
                                            <select name="village_id" id="village_id" class="form-control">
                                                <option value="">Choose Village</option>
                                                <?php foreach ($villages as $village): ?>
                                                <option value="<?= $village['id'] ?>" <?= $village['id']==$project['village_id']?'selected':'' ?>><?= $village['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Pin Code</label>
                                            <input type="number" value="<?= $project['pin_code'] ?>" name="pin_code" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Project Address</label>
                                            <input type="text" value="<?= $project['project_address'] ?>" name="project_address" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <h3>Work Order Details</h3>
                                <!-- Wrapper where all sections go -->
                                <div id="workOrderContainer">
                                    <?php foreach ($work_orders as $index=>$wo): ?>
                                    <div class="work-order-section border p-3 mb-3">
                                        <input type="hidden" name="work_order_id[]" value="<?= $wo['id'] ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Work Order Number</label>
                                                <input type="text" value="<?= $wo['work_order_number'] ?>" name="work_order_number[]" class="form-control" placeholder="Issued work order no">
                                            </div>
                                            <div class="col-md-6">
                                                <label>Work Order Date</label>
                                                <input type="date" value="<?= htmlspecialchars($wo['work_order_date']) ?>" name="work_order_date[]" class="form-control" >
                                            </div>
                                            <div class="col-md-6">
                                                <label>Work Order Amount</label>
                                                <input type="text" value="<?= $wo['work_order_amount'] ?>" name="work_order_amount[]" class="form-control workorder_amount" placeholder="Total value of work order">
                                                <input type="hidden" name="work_order_cess_amount" value="<?= $wo['work_order_cess_amount'] ?>" >
                                            </div>
                                            <div class="col-md-6">
                                                <label>Approval Letter</label>
                                                <input type="file" name="work_order_approval_letter[]" class="form-control">
                                                <?php if ($wo['work_order_approval_letter']): ?>
                                                    <p>Existing: <a href="../uploads/work_orders/<?= $wo['work_order_approval_letter'] ?>" target="_blank"><?= $wo['work_order_approval_letter'] ?></a></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Managers</label>
                                                <select name="work_order_manager_id[]" class="form-control">
                                                    <option value="">Choose Manager</option>
                                                    <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= $user['id']==$wo['manager_id']?'selected':'' ?>><?= $user['name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Engineers</label>
                                                <select name="work_order_engineer_id[]" class="form-control">
                                                    <option value="">Choose Engineer</option>
                                                    <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= $user['id']==$wo['engineer_id']?'selected':'' ?>><?= $user['name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Employer</label>
                                                <select name="work_order_employer_id[]" class="form-control">
                                                    <option value="">Choose Employer</option>
                                                    <?php foreach ($employers as $employee): ?>
                                                    <option value="<?= $employee['id'] ?>" <?= $employee['id']==$wo['employer_id']?'selected':'' ?>><?= $employee['name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-danger btn-sm mt-2 float-right remove-section">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Add More Button -->
                                <button type="button" class="btn btn-primary btn-sm float-right mb-4" id="addMoreBtn">+ Add More</button>
                                
                                <br/><br/>
                                <button type="submit" class="btn btn-info">Submit</button>
                                <a href="projects.php" class="btn btn-default ml-2">Cancel</a>
                                      
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

    // Get references to the input fields
    const constructionCostInput = document.getElementById('construction_cost');
    const cessAmountInput = document.getElementById('cess_amount');

    /**
     * Calculates the cess amount (1% of the construction cost)
     * and updates the cess amount input field.
     */
    function calculateCess() {
        // Get the value from the construction cost input
        const cost = parseFloat(constructionCostInput.value);

        // Check if the input is a valid number
        if (!isNaN(cost) && cost >= 0) {
            // Calculate 1% of the cost
            const cessAmount = cost * 0.01;

            // Update the cess amount input field with the calculated value.
            // We use toFixed(2) to format it to two decimal places for currency.
            cessAmountInput.value = cessAmount.toFixed(2);
        } else {
            // If the input is not a valid number, clear the cess amount field
            cessAmountInput.value = '';
        }
    }

    function getTotalWorkOrderAmount() {
        let total = 0;
        document.querySelectorAll('.workorder_amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        return total;
    }

    // Restrict while typing (live validation)
    document.addEventListener("input", function (e) {
        if (e.target.classList.contains("workorder_amount")) {
            const constructionCost = parseFloat(document.getElementById('construction_cost').value) || 0;
            const total = getTotalWorkOrderAmount();

            if (total > constructionCost) {
            alert("Total workorder amount cannot exceed construction cost!");
            e.target.value = "";
            }
        }
    });

    // Add an event listener to the construction cost input field.
    // The 'input' event fires whenever the value of the element changes.
    constructionCostInput.addEventListener('input', calculateCess);

    document.getElementById('addMoreBtn').addEventListener('click', function () {

        const constructionCost = parseFloat(document.getElementById('construction_cost').value) || 0;
        const total = getTotalWorkOrderAmount();
        if (total >= constructionCost) {
            alert("Cannot add more work orders. Construction cost limit reached!");
            return;
        }

        const container = document.getElementById('workOrderContainer');
        const sections = container.getElementsByClassName('work-order-section');
        const lastSection = sections[sections.length - 1];
        const newSection = lastSection.cloneNode(true);

        // Reset values inside cloned section
        newSection.querySelectorAll('input, select').forEach(el => {
            if (el.type === 'file') {
            el.value = null;
            } else {
            el.value = '';
            }
        });

        container.appendChild(newSection);
    });

    // Delegate remove button functionality
    document.getElementById('workOrderContainer').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-section')) {
            const sections = this.getElementsByClassName('work-order-section');
            if (sections.length > 1) {
            e.target.closest('.work-order-section').remove();
            } else {
            alert('At least one Work Order section is required.');
            }
        }
    });
    

    // Get references to date inputs
    const startDateInput = document.querySelector('input[name="project_start_date"]');
    const endDateInput = document.querySelector('input[name="project_end_date"]');
    const form = document.querySelector('form');

    // Function to validate dates
    function validateProjectDates() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDateInput.value && endDateInput.value) {
            if (endDate < startDate) {
                alert("❌ Project End Date cannot be earlier than Start Date.");
                endDateInput.value = ""; // reset invalid value
                endDateInput.focus();
                return false;
            }
        }
        return true;
    }

    // Trigger validation when end date changes
    endDateInput.addEventListener('change', validateProjectDates);

    // Also validate on form submit
    form.addEventListener('submit', function (e) {
        if (!validateProjectDates()) {
            e.preventDefault(); // stop form submission
        }
    });
</script>
</body>
</html>
