<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../config/db.php'; // adjust path if needed


$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM districts WHERE id = $id");
$district = mysqli_fetch_assoc($result);
// $id = $_GET['id']; // district id
// $sql = "SELECT d.*, s.name AS state_name 
//         FROM districts d
//         JOIN states s ON d.state_id = s.id
//         WHERE d.id = ?";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("i", $id);
// $stmt->execute();
// $result = $stmt->get_result();
// $district = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $state_id = intval($_POST['state_id']);
    $status = intval($_POST['active_status']);

    $query = "UPDATE districts SET name='$name', state_id=$state_id, active_status=$status, updated_by=1 WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        header("Location: districts.php?msg=District Updated Successfully");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>MBOCWCESS Portal | Edit District</title>
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
                            <h1 class="m-0 text-dark">Edit District</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Edit District</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->
<?php //echo '<pre>'; print_r($district); die;?>
            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <form method="post">
                                <div class="form-group">
                                    <label>District Name</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= htmlspecialchars($district['name']) ?>" required>
                                </div>
                               
                                <div class="form-group">
                                    <label>State Name</label>
                                    <input type="text" class="form-control" value="Maharashtra" readonly>
                                    <input type="hidden" name="state_id" value="21">
                                </div>                                
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="active_status" class="form-control">
                                        <option value="1" <?= $district['active_status'] == 1 ? 'selected' : '' ?>>Active
                                        </option>
                                        <option value="2" <?= $district['active_status'] == 2 ? 'selected' : '' ?>>Inactive
                                        </option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Update</button>
                                <a href="districts.php" class="btn btn-secondary">Back</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>