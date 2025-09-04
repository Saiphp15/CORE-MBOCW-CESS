<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
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

  <title>MBOCWCESS Portal | Add Bulk Projects Invoce Cess</title>
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
            <h1 class="m-0 text-dark">Add Bulk Projects Invoce Cess</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Add Bulk Projects Invoce Cess</li>
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
                    <h3 class="card-title">Add Bulk Projects Invoce Cess Information</h3>
                    <div class="card-tools">
                        <a href="../assets/projects-invoice-cess-template/projects-invoices-cess-template.xlsx" class="btn btn-info" download><i class="fas fa-download"></i> Download Sample Template</a> 
                        <a href="bulk-invoices-history.php" class="btn btn-primary" ><i class="fas fa-eye"></i> Bulk Invoice Upload History List</a> 
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
                            <form action="save-bulk-projects-invoces-cess.php" method="post" enctype="multipart/form-data">
                                <h3>Bulk Projects Invoce Cess Information</h3>
                                <div class="row">
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Effective Cess Amount</label> (<i>It Could be vary while upload validation process</i>)
                                            <input type="number" name="effective_cess_amount" id="effective_cess_amount" placeholder="Effective Cess Amount" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Upload Bulk Projects Invoice CESS CSV</label>
                                            <input type="file" name="bulk_projects_invoices_cess" id="bulk_projects_invoices_cess" class="form-control" required>
                                        </div>
                                    </div>

                                </div>

                                <br/><br/>
                                <button type="submit" class="btn btn-success">Submit</button>
                                <a href="bulk-invoices-history.php" class="btn btn-default ml-2">Cancel</a>
                                      
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
<!-- XLSX.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<!-- page script -->
<script>
  // Get references to the file input and the effective cess amount input
  const xlsxFileInput = document.getElementById('bulk_projects_invoices_cess');
  const effectiveCessAmountInput = document.getElementById('effective_cess_amount');

  // Add a 'change' event listener to the file input
  xlsxFileInput.addEventListener('change', function(event) {
    // Clear the input field if no file is selected
    effectiveCessAmountInput.value = '';

    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();

      // This function is executed when the file is successfully read
      reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        
        // Assuming the data is in the first sheet
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];
        
        // Convert the worksheet to a JSON array of objects
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

        // The Effective Cess Amount is at index 11 (0-indexed)
        const effectiveCessAmountColumnIndex = 11;

        let totalEffectiveCessAmount = 0;

        // Loop through all data rows (starting from index 1 to skip the header)
        for (let i = 1; i < jsonData.length; i++) {
          const rowData = jsonData[i];
          
          // Check if the row has a value at the expected index
          if (rowData.length > effectiveCessAmountColumnIndex) {
            const effectiveCessAmount = parseFloat(rowData[effectiveCessAmountColumnIndex]);
            
            if (!isNaN(effectiveCessAmount) && effectiveCessAmount > 0) {
              // get effective cess amount for this row and add it to the total
              totalEffectiveCessAmount += effectiveCessAmount;
            }
          }
        }

        // Update the input field with the calculated total
        effectiveCessAmountInput.value = totalEffectiveCessAmount.toFixed(2);
        console.log('Total Cess Amount calculated from Excel:', totalEffectiveCessAmount.toFixed(2));
      };
      
      // Read the file as a binary array
      reader.readAsArrayBuffer(file);
    }
  });
</script>

</body>
</html>
