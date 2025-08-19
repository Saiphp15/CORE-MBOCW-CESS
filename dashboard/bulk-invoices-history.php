<?php
// bulk-invoices-history.php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include your database connection file
require_once '../config/db.php';

function getPaymentModeName($mode) {
    switch ($mode) {
        case '1': return 'Online';
        case '2': return 'Offline';
        case '3': return 'Exempted';
        default: return 'Unknown';
    }
}

// You will need to add a new function for the status to handle three states
function getPaymentStatus($status) {
    switch ($status) {
        case '1': return '<span class="badge bg-success">Verified</span>';
        case '2': return '<span class="badge bg-warning">Pending</span>';
        case '3': return '<span class="badge bg-danger">Rejected</span>';
        default: return '<span class="badge bg-secondary">Unknown</span>';
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

  <title>MBOCWCESS Portal | Bulk Invoice Upload History</title>

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
            <h1 class="m-0 text-dark">Bulk Invoice Upload History List</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Bulk Invoice Upload History List</li>
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
                <h3 class="card-title">Bulk Invoice Upload History List</h3>
                <div class="card-tools">
                    <a href="bulk-projects-invoice-cess-upload-form.php" class="btn btn-info" ><i class="fas fa-plus"></i> Bulk Projects Upload</a>
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
                            <th>Total Effective Cess</th>
                            <th>Bulk Uploaded File</th>
                            <th>Payment Mode</th>
                            <th>Uploaded On</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT 
                                        bpih.id,
                                        bpih.effective_cess_amount,
                                        bpih.bulk_project_invoices_template_file,
                                        bpih.cess_payment_mode,
                                        bpih.is_payment_verified,
                                        bpih.rejection_reason,
                                        bpih.created_at
                                    FROM bulk_projects_invoices_history AS bpih
                                    ORDER BY bpih.created_at DESC";
                        $result = mysqli_query($conn, $sql);
                        $sr = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr id='row-{$row['id']}'>";
                                echo "<td>{$sr}</td>";
                                echo "<td>₹" . htmlspecialchars(number_format($row['effective_cess_amount'], 2)) . "</td>";
                                echo "<td>" . htmlspecialchars($row['bulk_project_invoices_template_file']) . "</td>";
                                echo "<td>" . htmlspecialchars(getPaymentModeName($row['cess_payment_mode'])) . "</td>";
                                echo "<td>" . htmlspecialchars(date("Y-m-d", strtotime($row['created_at']))) . "</td>";
                                
                                // Display status with a badge
                                if ($row['is_payment_verified'] == 3) {
                                    // Add tooltip for rejected payments
                                    $tooltip = htmlspecialchars($row['rejection_reason']);
                                    echo "<td class='payment-status'><span class='badge bg-danger' data-toggle='tooltip' title='{$tooltip}'>Rejected</span></td>";
                                } else {
                                    $status = getPaymentStatus($row['is_payment_verified']);
                                    echo "<td class='payment-status'>" . $status . "</td>";
                                }
                                
                                echo "<td class='actions'>";
                                echo "<a href='view-bulk-invoice.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'><i class='fas fa-eye'></i></a>";
                                
                                // Only show action buttons if status is 'Pending'
                                if ($row['is_payment_verified'] == 2) {
                                    echo " <button class='btn btn-sm btn-success verify-btn' data-id='{$row['id']}'><i class='fas fa-check'></i> Verify</button>";
                                    echo " <button class='btn btn-sm btn-danger reject-btn' data-id='{$row['id']}' data-toggle='modal' data-target='#rejectModal'><i class='fas fa-times'></i> Reject</button>";
                                }

                                echo "</td>";
                                echo "</tr>";
                                $sr++;
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No bulk invoice history found.</td></tr>";
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

  <!-- Reject Payment Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="invoiceIdToReject">
                <div class="form-group">
                    <label for="rejectionReason">Rejection Reason</label>
                    <textarea class="form-control" id="rejectionReason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject</button>
            </div>
        </div>
    </div>
  </div>

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
        
        // Initialize tooltips on the page
        $('[data-toggle="tooltip"]').tooltip();

        // Handle verify button click
        $(document).on('click', '.verify-btn', function() {
            var invoiceId = $(this).data('id');
            if (confirm("Are you sure you want to verify this payment?")) {
                $.ajax({
                    url: 'update-invoice-status.php',
                    type: 'POST',
                    data: {
                        id: invoiceId,
                        status: 'verified'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            var row = $('#row-' + invoiceId);
                            row.find('.payment-status').html('<span class="badge bg-success">Verified</span>');
                            row.find('.actions .verify-btn, .actions .reject-btn').hide();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while verifying the payment.');
                    }
                });
            }
        });

        // Handle reject button click to populate modal
        $(document).on('click', '.reject-btn', function() {
            var invoiceId = $(this).data('id');
            $('#invoiceIdToReject').val(invoiceId);
            $('#rejectionReason').val(''); // Clear the reason textarea
        });

        // Handle confirm reject button click inside modal
        $('#confirmRejectBtn').on('click', function() {
            var invoiceId = $('#invoiceIdToReject').val();
            var rejectionReason = $('#rejectionReason').val();

            if (rejectionReason.trim() === '') {
                alert('Please provide a reason for rejection.');
                return;
            }

            $.ajax({
                url: 'update-invoice-status.php',
                type: 'POST',
                data: {
                    id: invoiceId,
                    status: 'rejected',
                    reason: rejectionReason
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#rejectModal').modal('hide');
                        var row = $('#row-' + invoiceId);
                        var tooltip = 'data-toggle="tooltip" title="' + rejectionReason + '"';
                        row.find('.payment-status').html('<span class="badge bg-danger"' + tooltip + '>Rejected</span>');
                        row.find('.actions .verify-btn, .actions .reject-btn').hide();
                        $('[data-toggle="tooltip"]').tooltip(); // Re-initialize tooltips
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while rejecting the payment.');
                }
            });
        });
    });
</script>
</body>
</html>