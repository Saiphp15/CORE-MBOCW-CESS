<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php'; // adjust path if needed

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

  <title>MBOCWCESS Portal | Roles</title>
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
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 36px;   /* reduced from 60px */
            height: 20px;  /* reduced from 34px */
            vertical-align: middle; /* align with icons */
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 20px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;  /* slightly smaller than track */
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
            transform: translateX(16px); /* match smaller size */
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
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
            <h1 class="m-0 text-dark">Permissions</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Permissions</li>
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
                <h3 class="card-title">Permissions</h3>
                <div class="card-tools">
                    <a href="add-permission.php" class="btn btn-primary" ><i class="fas fa-plus"></i> Add Permission</a>
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
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM permissions ORDER BY id ASC";
                        $result = mysqli_query($conn, $sql);
                        $sr = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $isActive = ($row['is_active'] == 1) ? 'checked' : '';
                                $statusText = ($row['is_active'] == 1) ? 'Active' : 'Inactive';

                                echo "<tr>";
                                echo "<td>".$sr."</td>";
                                echo "<td>".htmlspecialchars($row['name'])."</td>";
                                echo "<td>".htmlspecialchars($row['description'])."</td>";
                                echo "<td>
                                        <label class='switch' title='$statusText'><input type='checkbox' data-id='{$row['id']}' id='activeToggle' onclick='return confirm(\"Are you sure you want to perform this action?\");' {$isActive}><span class='slider round'></span></label>
                                        <a href='edit-permission.php?id=".$row['id']."' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a>
                                        <a href='view-permission.php?id=".$row['id']."' class='btn btn-sm btn-info' ><i class='fas fa-eye'></i></a>
                                    </td>";
                                echo "</tr>";
                                $sr++;
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No Permission found</td></tr>";
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

    $(document).on('change', '#activeToggle', function() {
        let id = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 2; // 1 for Active, 2 for Inactive

        $.ajax({
            url: "toggle-permission.php",
            type: "POST",
            data: { id: id, status: status },
            dataType: "json",
            success: function(response) {
                if(response.success){
                    alert("Status updated successfully!");
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr, status, error){
                alert("AJAX Error: " + error);
            }
        });
    });
</script>
</body>
</html>
