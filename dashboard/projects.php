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
                    <a href="bulk-projects-invoice-cess-upload-form.php" class="btn btn-info" ><i class="fas fa-plus"></i> Bulk Projects Upload</a>
                    <a href="add-project.php" class="btn btn-primary" ><i class="fas fa-plus"></i> Add Project</a> 
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove" data-toggle="tooltip" title="Remove"><i class="fas fa-times"></i></button>
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
                            <th>Category</th>
                            <th>Type</th>
                            <th>Authority</th>
                            <th>Cost</th>
                            <th>Cess</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "
                            SELECT
                                p.*,
                                IFNULL(SUM(pwo.work_order_effective_cess_amount), 0) AS total_cess,
                                pt.name AS project_type_name,
                                pc.name AS project_category_name,
                                la.name AS local_authority_name
                            FROM
                                projects AS p
                            LEFT JOIN
                                project_types AS pt ON p.project_type_id = pt.id
                            LEFT JOIN
                                project_categories AS pc ON p.project_category_id = pc.id
                            LEFT JOIN 
                                local_authorities AS la ON p.local_authority_id = la.id
                            LEFT JOIN
                                project_work_orders AS pwo ON p.id = pwo.project_id
                            GROUP BY
                                p.id, pt.name, pc.name
                            ORDER BY
                                p.id DESC
                        ";
                        $result = mysqli_query($conn, $sql);
                        $sr = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$sr}</td>";
                                echo "<td>" . htmlspecialchars($row['project_name'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_category_name'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($row['project_type_name'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars($row['local_authority_name'] ?? '') . "</td>";
                                echo "<td>₹" . number_format($row['construction_cost'], 2) . "</td>";
                                echo "<td>₹" . number_format($row['total_cess'] ?? 0, 2) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td>
                                        <a href='view-project.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'><i class='fas fa-eye'></i></a>
                                        <a href='edit-project.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a>
                                    </td>";
                                echo "</tr>";
                                $sr++;
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No project found</td></tr>";
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
