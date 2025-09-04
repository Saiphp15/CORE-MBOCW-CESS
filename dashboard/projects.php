<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php'; // adjust path if needed
function getPaymentModeName($mode) {
    switch ($mode) {
        case '1': return 'Cash';
        case '2': return 'Net Banking';
        case '3': return 'UPI';
        case '4': return 'Credit Card';
        case '5': return 'Debit Card';
        case '6': return 'Cheque';
        default: return '0';
    }
}
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

  <title>MBOCWCESS Portal | Projects List</title>
  <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
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
            <h1 class="m-0 text-dark">Project List</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Project List</li>
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
                <h3 class="card-title">Project List</h3>
                <div class="card-tools">
                    <?php if(in_array($_SESSION['user_role'],[3,7])){ ?>
                    <a href="bulk-projects-invoice-cess-upload-form.php" class="btn btn-info" ><i class="fas fa-plus"></i> Bulk Projects Upload</a>
                    <a href="add-project.php" class="btn btn-primary" ><i class="fas fa-plus"></i> Add Project</a> 
                    <?php } ?>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
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
                <table id="example1" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sr.No</th>
                            <th>Name</th>
                            <th>Cost</th>
                            <th>Cess</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $loggedInUserId     = $_SESSION['user_id'];
                        $loggedInUserRole   = $_SESSION['user_role'];
                        $sql = "";
                        if ($loggedInUserId == 1 && $loggedInUserRole == 1) {
                            // Superadmin: see all
                            $sql = "SELECT * FROM projects AS p GROUP BY p.id ORDER BY p.id DESC";
                        } elseif ($loggedInUserRole == 3) {
                            // CAFO: see own + engineers under him
                            $sql = "SELECT * FROM projects AS p WHERE p.created_by = $loggedInUserId 
                                    OR p.created_by IN (
                                        SELECT id FROM users WHERE created_by = $loggedInUserId
                                    )
                                    GROUP BY p.id ORDER BY p.id DESC";
                        } elseif ($loggedInUserRole == 7) {
                            // Engineer: see only his own
                            $sql = "SELECT * FROM projects AS p WHERE p.created_by = $loggedInUserId GROUP BY p.id ORDER BY p.id DESC";
                        }
                        
                        $result = mysqli_query($conn, $sql);
                        $sr = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$sr}</td>";
                                echo "<td>" . htmlspecialchars($row['project_name'] ?? '') . "</td>";
                                echo "<td>₹" . number_format($row['construction_cost'], 2) . "</td>";
                                echo "<td>₹" . number_format($row['cess_amount'] ?? 0, 2) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td>
                                        <a href='view-project.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'><i class='fas fa-eye'></i></a>
                                        <a href='edit-project.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a>
                                    </td>";
                                echo "</tr>";
                                $sr++;
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No project found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
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
<!-- DataTables -->
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>
<!-- page script -->
<script>
  $(function () {
    $('#example1').DataTable({
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf']
    });
  });
</script>
</body>
</html>
