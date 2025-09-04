<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include your database connection file
require_once '../config/db.php';

// Initialize data arrays
$projectReports = [];
$workOrderReports = [];
$projectStatusCards = [];

// Check if database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // --- 1. Project-wise CESS Report Query ---
    $loggedInUserId     = $_SESSION['user_id'];
    $loggedInUserRole   = $_SESSION['user_role'];
    $sqlProjectReports = "";
    if ($loggedInUserId == 1 && $loggedInUserRole == 1) {
        // Superadmin: see all
        $sqlProjectReports = "
            SELECT 
                p.id,
                p.project_name,
                (select SUM(pwo.work_order_effective_cess_amount) from project_work_orders pwo where pwo.project_id=p.id) AS total_cess,
                COALESCE(SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END), 0) AS collected_cess
            FROM 
                projects p
            LEFT JOIN 
                project_work_orders pwo ON p.id = pwo.project_id
            LEFT JOIN
                cess_payment_history cph ON pwo.id = cph.workorder_id
            GROUP BY 
                p.id, p.project_name
            ORDER BY
                p.project_name;
        ";
    } elseif ($loggedInUserRole == 3) {
        // CAFO: see own + engineers under him
        $sqlProjectReports = "
            SELECT 
                p.id,
                p.project_name,
                (select SUM(pwo.work_order_effective_cess_amount) from project_work_orders pwo where pwo.project_id=p.id) AS total_cess,
                COALESCE(SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END), 0) AS collected_cess
            FROM 
                projects p
            LEFT JOIN 
                project_work_orders pwo ON p.id = pwo.project_id
            LEFT JOIN
                cess_payment_history cph ON pwo.id = cph.workorder_id
            WHERE 
                p.created_by = $loggedInUserId 
                OR p.created_by IN (
                    SELECT id FROM users WHERE created_by = $loggedInUserId
                )
            GROUP BY 
                p.id, p.project_name
            ORDER BY
                p.project_name;
        ";
    } elseif ($loggedInUserRole == 7) {
        // Engineer: see only his own
        $sqlProjectReports = "
            SELECT 
                p.id,
                p.project_name,
                (select SUM(pwo.work_order_effective_cess_amount) from project_work_orders pwo where pwo.project_id=p.id) AS total_cess,
                COALESCE(SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END), 0) AS collected_cess
            FROM 
                projects p
            LEFT JOIN 
                project_work_orders pwo ON p.id = pwo.project_id
            LEFT JOIN
                cess_payment_history cph ON pwo.id = cph.workorder_id
            WHERE 
                p.created_by = $loggedInUserId
            GROUP BY 
                p.id, p.project_name
            ORDER BY
                p.project_name;
        ";
    }
    
    $resultProjectReports = $conn->query($sqlProjectReports);
    if ($resultProjectReports && $resultProjectReports->num_rows > 0) {
        while ($row = $resultProjectReports->fetch_assoc()) {
            $row['pending_cess'] = $row['total_cess'] - $row['collected_cess'];
            $projectReports[] = $row;
        }
    }

    // --- 2. Work Order-wise CESS Report Query ---
    $loggedInUserId     = $_SESSION['user_id'];
    $loggedInUserRole   = $_SESSION['user_role'];
    $sqlWorkOrderReports = "";
    if ($loggedInUserId == 1 && $loggedInUserRole == 1) {
        // Superadmin: see all
        $sqlWorkOrderReports = "
            SELECT 
                pwo.id AS work_order_id,
                pwo.work_order_number,
                pwo.work_order_effective_cess_amount AS total_cess,
                COALESCE(SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END), 0) AS collected_cess
            FROM 
                project_work_orders pwo
            LEFT JOIN
                cess_payment_history cph ON pwo.id = cph.workorder_id
            GROUP BY 
                pwo.id, pwo.work_order_number, pwo.work_order_effective_cess_amount
            ORDER BY
                pwo.work_order_number;
        ";
    } elseif ($loggedInUserRole == 3) {
        // CAFO: see own + engineers under him
        $sqlWorkOrderReports = "
            SELECT 
                pwo.id AS work_order_id,
                pwo.work_order_number,
                pwo.work_order_effective_cess_amount AS total_cess,
                COALESCE(SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END), 0) AS collected_cess
            FROM 
                project_work_orders pwo
            LEFT JOIN
                projects p ON pwo.project_id = p.id
            LEFT JOIN
                cess_payment_history cph ON pwo.id = cph.workorder_id
            WHERE 
                p.created_by = $loggedInUserId 
                OR p.created_by IN (
                    SELECT id FROM users WHERE created_by = $loggedInUserId
                )
            GROUP BY 
                pwo.id, pwo.work_order_number, pwo.work_order_effective_cess_amount
            ORDER BY
                pwo.work_order_number;
        ";
    } elseif ($loggedInUserRole == 7) {
        // Engineer: see only his own
        $sqlWorkOrderReports = "
            SELECT 
                pwo.id AS work_order_id,
                pwo.work_order_number,
                pwo.work_order_effective_cess_amount AS total_cess,
                COALESCE(SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END), 0) AS collected_cess
            FROM 
                project_work_orders pwo
            LEFT JOIN
                projects p ON pwo.project_id = p.id
            LEFT JOIN
                cess_payment_history cph ON pwo.id = cph.workorder_id
            WHERE 
                p.created_by = $loggedInUserId
            GROUP BY 
                pwo.id, pwo.work_order_number, pwo.work_order_effective_cess_amount
            ORDER BY
                pwo.work_order_number;
        ";
    }
    $resultWorkOrderReports = $conn->query($sqlWorkOrderReports);
    if ($resultWorkOrderReports && $resultWorkOrderReports->num_rows > 0) {
        while ($row = $resultWorkOrderReports->fetch_assoc()) {
            $row['pending_cess'] = $row['total_cess'] - $row['collected_cess'];
            $workOrderReports[] = $row;
        }
    }

    // --- 3. Project Status & CESS Collection Cards Data ---
    $loggedInUserId     = $_SESSION['user_id'];
    $loggedInUserRole   = $_SESSION['user_role'];
    $sqlProjectStatus = "";
    if ($loggedInUserId == 1 && $loggedInUserRole == 1) {
        // Superadmin: see all
        $sqlProjectStatus = "SELECT id, project_name FROM projects ORDER BY project_name;";
    } elseif ($loggedInUserRole == 3) {
        // CAFO: see own + engineers under him
        $sqlProjectStatus = "
            SELECT id, project_name 
            FROM projects 
            WHERE created_by = $loggedInUserId 
                OR created_by IN (
                    SELECT id FROM users WHERE created_by = $loggedInUserId
                )
            ORDER BY project_name;
        ";
    } elseif ($loggedInUserRole == 7) {
        // Engineer: see only his own
        $sqlProjectStatus = "SELECT id, project_name FROM projects WHERE created_by = $loggedInUserId ORDER BY project_name;";
    }
    $resultProjectStatus = $conn->query($sqlProjectStatus);
    if ($resultProjectStatus && $resultProjectStatus->num_rows > 0) {
        while ($project = $resultProjectStatus->fetch_assoc()) {
            $projectId = $project['id'];
            $projectName = $project['project_name'];

            // Get work order counts for project completion status
            $sqlWorkOrderCounts = "SELECT COUNT(*) AS total, SUM(CASE WHEN LOWER(status) = 'Completed' THEN 1 ELSE 0 END) AS completed FROM project_work_orders WHERE project_id = ?";
            $stmt = $conn->prepare($sqlWorkOrderCounts);
            $stmt->bind_param("i", $projectId);
            $stmt->execute();
            $result = $stmt->get_result();
            $counts = $result->fetch_assoc();
            $totalWorkOrders = $counts['total'];
            $completedWorkOrders = $counts['completed'];
            $projectStatusPercent = ($totalWorkOrders > 0) ? ($completedWorkOrders / $totalWorkOrders) * 100 : 0;
            $stmt->close();

            // Get CESS amounts for CESS collection progress
            $loggedInUserId     = $_SESSION['user_id'];
            $loggedInUserRole   = $_SESSION['user_role'];
            $sqlCessAmounts = "
                SELECT 
                    pwo.id,
                    pwo.work_order_number,
                    pwo.work_order_effective_cess_amount AS total_cess,
                    SUM(CASE WHEN cph.is_payment_verified = 1 AND LOWER(cph.payment_status) = 'Paid' THEN cph.effective_cess_amount ELSE 0 END) AS collected_cess
                FROM project_work_orders pwo
                LEFT JOIN cess_payment_history cph ON pwo.id = cph.workorder_id
                WHERE pwo.project_id = ?
                GROUP BY 
                    pwo.id, pwo.work_order_number, pwo.work_order_effective_cess_amount
            ";
            
            $stmt = $conn->prepare($sqlCessAmounts);
            $stmt->bind_param("i", $projectId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Initialize total and collected amounts before looping
            $totalCessDue = 0;
            $collectedCess = 0;
            
            while ($cessAmounts = $result->fetch_assoc()) {
                $totalCessDue += $cessAmounts['total_cess'];
                $collectedCess += $cessAmounts['collected_cess'];
            }
            
            $cessCollectionPercent = ($totalCessDue > 0) ? ($collectedCess / $totalCessDue) * 100 : 0;
            $stmt->close();

            $projectStatusCards[] = [
                'id' => $projectId,
                'name' => $projectName,
                'status_percent' => round($projectStatusPercent, 2),
                'cess_percent' => round($cessCollectionPercent, 2),
                'total_cess_due' => number_format($totalCessDue, 2),
                'total_collected' => number_format($collectedCess, 2),
                'total_pending' => number_format($totalCessDue - $collectedCess, 2)
            ];
        }
    }
} catch (Exception $e) {
    error_log("Database error on reports page: " . $e->getMessage());
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>MBOCWCESS Portal | Reports</title>
    <link rel="icon" href="../assets/img/favicon_io/favicon.ico" type="image/x-icon">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- DataTables Buttons CSS (New) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
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
            <h1 class="m-0 text-dark">Reports</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Reports</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">

        <div class="row">
            <div class="col-md-6">
                <!-- Project-wise CESS Report -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Project-wise CESS Report</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($projectReports) > 0): ?>
                        <table id="projectReportsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                            <th>Project Name</th>
                            <th>Total CESS Due (₹)</th>
                            <th>CESS Collected (₹)</th>
                            <th>CESS Pending (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projectReports as $report): ?>
                            <tr>
                            <td><?php echo htmlspecialchars($report['project_name']); ?></td>
                            <td><?php echo number_format($report['total_cess'], 2); ?></td>
                            <td><?php echo number_format($report['collected_cess'], 2); ?></td>
                            <td><?php echo number_format($report['pending_cess'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                        <?php else: ?>
                        <p>No project data found. Please ensure you have projects and work orders in your database.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-6">
                <!-- Work Order-wise CESS Report -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Work Order-wise CESS Report</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($workOrderReports) > 0): ?>
                        <table id="workOrderReportsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                            <th>Work Order #</th>
                            <th>Total CESS Due (₹)</th>
                            <th>CESS Collected (₹)</th>
                            <th>CESS Pending (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workOrderReports as $report): ?>
                            <tr>
                            <td><?php echo htmlspecialchars($report['work_order_number']); ?></td>
                            <td><?php echo number_format($report['total_cess'], 2); ?></td>
                            <td><?php echo number_format($report['collected_cess'], 2); ?></td>
                            <td><?php echo number_format($report['pending_cess'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                        <?php else: ?>
                        <p>No work order data found. Please ensure you have work orders in your database.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
        
        <!-- Project Status Cards -->
        <div class="card card-info">
          <div class="card-header">
            <h3 class="card-title">Project Status & CESS Collection</h3>
          </div>
          <div class="card-body">
                <div class="min-h-screen flex flex-col ">
                    <div class="mx-auto  ">
                        <!-- Project Status Cards Container -->
                        <div id="project-cards-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Cards will be injected here by JavaScript -->
                            <div class="col-span-1 sm:col-span-2 lg:col-span-3 text-center py-10" id="loading-message">
                                <span class="text-gray-500">Loading projects...</span>
                            </div>
                        </div>
                        <!-- Pagination Controls -->
                        <div id="pagination-container" class="flex justify-center mt-8 space-x-2">
                            <!-- Pagination buttons will be injected here by JavaScript -->
                        </div>
                    </div>
                </div>
          </div>
        </div>
        <!-- /.card -->

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
<!-- DataTables Buttons JS (New) -->
<script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.colVis.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>
<!-- page script -->
<script>
  $(function () {
    $('#projectReportsTable').DataTable({
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf']
    });
    $('#workOrderReportsTable').DataTable({
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf']
    });
  });
</script>
<!-- JavaScript for Pagination Logic -->
<script>
    // Data passed from PHP to JavaScript
    const projectData = <?php echo json_encode($projectStatusCards); ?>;

    const cardsContainer = document.getElementById('project-cards-container');
    const paginationContainer = document.getElementById('pagination-container');
    const loadingMessage = document.getElementById('loading-message');

    const cardsPerPage = 8;
    let currentPage = 1;

    // Function to render the project cards for the current page
    function renderCards() {
        if (projectData.length === 0) {
            loadingMessage.innerText = 'No project data found. Please ensure you have projects in your database.';
            return;
        }

        loadingMessage.classList.add('hidden');
        cardsContainer.innerHTML = ''; // Clear previous cards

        const start = (currentPage - 1) * cardsPerPage;
        const end = start + cardsPerPage;
        const paginatedCards = projectData.slice(start, end);

        paginatedCards.forEach(card => {
            const cardHtml = `
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <a href='view-project.php?id=${card.id}' title='Clcik to view details'>
                        <h5 class="font-semibold text-gray-800 mb-4">${card.name}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-sm font-medium text-gray-600 mb-1">
                                    Project Status (<span class="font-bold text-blue-600">${card.status_percent}%</span>)
                                </p>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: ${card.status_percent}%"></div>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <p class="text-sm font-medium text-gray-600 mb-1">
                                    CESS Collection (<span class="font-bold text-green-600">${card.cess_percent}%</span>)
                                </p>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: ${card.cess_percent}%"></div>
                                </div>
                            </div>
                        </div>
                        <ul class="text-sm text-gray-700 mt-4 space-y-2">
                            <li><strong>Total CESS Due:</strong> ₹${card.total_cess_due}</li>
                            <li><strong>Total Collected:</strong> ₹${card.total_collected}</li>
                            <li><strong>Pending CESS:</strong> ₹${card.total_pending}</li>
                        </ul>
                    </a>
                </div>
            `;
            cardsContainer.insertAdjacentHTML('beforeend', cardHtml);
        });
    }

    // Function to render the pagination buttons
    function renderPagination() {
        paginationContainer.innerHTML = ''; // Clear previous pagination buttons
        const totalPages = Math.ceil(projectData.length / cardsPerPage);

        if (totalPages <= 1) return;

        // Previous button
        const prevButton = document.createElement('button');
        prevButton.innerText = 'Previous';
        prevButton.classList.add('px-4', 'py-2', 'rounded-md', 'border', 'border-gray-300', 'bg-white', 'text-gray-700', 'hover:bg-gray-100', 'transition-colors', 'duration-200', 'shadow-sm');
        if (currentPage === 1) prevButton.disabled = true;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderCards();
                renderPagination();
            }
        });
        paginationContainer.appendChild(prevButton);

        // Page number buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.innerText = i;
            pageButton.classList.add('px-4', 'py-2', 'rounded-md', 'shadow-sm', 'transition-colors', 'duration-200');
            if (i === currentPage) {
                pageButton.classList.add('bg-blue-600', 'text-white');
                pageButton.disabled = true;
            } else {
                pageButton.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-100', 'border', 'border-gray-300');
            }
            pageButton.addEventListener('click', () => {
                currentPage = i;
                renderCards();
                renderPagination();
            });
            paginationContainer.appendChild(pageButton);
        }

        // Next button
        const nextButton = document.createElement('button');
        nextButton.innerText = 'Next';
        nextButton.classList.add('px-4', 'py-2', 'rounded-md', 'border', 'border-gray-300', 'bg-white', 'text-gray-700', 'hover:bg-gray-100', 'transition-colors', 'duration-200', 'shadow-sm');
        if (currentPage === totalPages) nextButton.disabled = true;
        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderCards();
                renderPagination();
            }
        });
        paginationContainer.appendChild(nextButton);
    }

    // Initial render on page load
    document.addEventListener('DOMContentLoaded', () => {
        renderCards();
        renderPagination();
    });
</script>
</body>
</html>
