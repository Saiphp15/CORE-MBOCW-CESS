<?php 
  require_once '../common/helper.php';
  require_once '../config/db.php'; 
  $current_page = basename($_SERVER['PHP_SELF']); 
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link navbar-primary">
      <img src="../dist/img/MBOCWLogo.png" alt="MBOCW Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">MBOCW</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info" title="<?php echo $_SESSION['user_role_name']; ?>">
          <a href="javascript:void(0);" class="d-block"><?php echo $_SESSION['user_role_name']; ?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

          <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role']==1){ ?> 
            <li class="nav-item"><a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" title="Dashboard"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
            <li class="nav-item"><a href="authority-departments.php" class="nav-link <?php echo ($current_page == 'authority-departments.php') ? 'active' : ''; ?>" title="Manage Departments"><i class="far fa-building nav-icon"></i><p>Manage Departments</p></a></li>
            <li class="nav-item"><a href="authority-subdepartments.php" class="nav-link <?php echo ($current_page == 'authority-subdepartments.php') ? 'active' : ''; ?>" title="Manage Sub Department"><i class="far fa-building nav-icon"></i><p>Manage Sub Department</p></a></li>
            <li class="nav-item"><a href="local-authorities.php" class="nav-link <?php echo ($current_page == 'local-authorities.php') ? 'active' : ''; ?>" title="Manage Local Authority"><i class="far fa-building nav-icon"></i><p>Manage Local Authority</p></a></li>
            <li class="nav-item"><a href="projects.php" class="nav-link <?php echo ($current_page == 'projects.php') ? 'active' : ''; ?>" title="Manage Projects"><i class="far fa-building nav-icon"></i><p>Manage Projects</p></a></li>
            <li class="nav-item"><a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" title="Manage Users"><i class="far fa-user nav-icon"></i><p>Manage Users</p></a></li>
            <li class="nav-item"><a href="roles.php" class="nav-link <?php echo ($current_page == 'roles.php') ? 'active' : ''; ?>" title="Manage Roles"><i class="far fa-user nav-icon"></i><p>Manage Roles</p></a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link <?php echo ($current_page == 'permissions.php') ? 'active' : ''; ?>" title="Manage Permission"><i class="far fa-user nav-icon"></i><p>Manage Permission</p></a></li>
            <li class="nav-item"><a href="employers.php" class="nav-link <?php echo ($current_page == 'employers.php') ? 'active' : ''; ?>" title="Manage Employers"><i class="far fa-user nav-icon"></i><p>Manage Employer</p></a></li>
            <li class="nav-item"><a href="districts.php" class="nav-link <?php echo ($current_page == 'districts.php') ? 'active' : ''; ?>" title="Manage Districts"><i class="far fa-user nav-icon"></i><p>Manage Districts</p></a></li>
            <li class="nav-item"><a href="talukas.php" class="nav-link <?php echo ($current_page == 'talukas.php') ? 'active' : ''; ?>" title="Manage Talukas"><i class="far fa-user nav-icon"></i><p>Manage Talukas</p></a></li>
            <li class="nav-item"><a href="bulk-invoices-history.php" class="nav-link <?php echo ($current_page == 'bulk-invoices-history.php') ? 'active' : ''; ?>" title="Bulk Invoice Upload History"><i class="nav-icon fas fa-money-bill-wave"></i><p>Bulk Invoice Upload History</p></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" title="Reports"><i class="nav-icon fas fa-chart-line"></i><p>Reports</p></a></li>
          <?php }else{ ?> 
            <?php if (hasPermission('Dashboard')) { ?>
              <li class="nav-item"><a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" title="Dashboard"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Departments')) { ?>
            <li class="nav-item"><a href="authority-departments.php" class="nav-link <?php echo ($current_page == 'authority-departments.php') ? 'active' : ''; ?>" title="Manage Authority Departments"><i class="far fa-building nav-icon"></i><p>Manage Departments</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Sub Departments')) { ?>
            <li class="nav-item"><a href="authority-subdepartments.php" class="nav-link <?php echo ($current_page == 'authority-subdepartments.php') ? 'active' : ''; ?>" title="Manage Authority Sub Departments"><i class="far fa-building nav-icon"></i><p>Manage Sub Departments</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Local Authority')) { ?>
              <li class="nav-item"><a href="local-authorities.php" class="nav-link <?php echo ($current_page == 'local-authorities.php') ? 'active' : ''; ?>" title="Manage Local Authority"><i class="far fa-building nav-icon"></i><p>Manage Local Authority</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Projects')) { ?>
                <li class="nav-item"><a href="projects.php" class="nav-link <?php echo ($current_page == 'projects.php') ? 'active' : ''; ?>" title="Manage Projects"><i class="far fa-building nav-icon"></i><p>Manage Projects</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Users')) { ?>
                <li class="nav-item"><a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" title="Manage Users"><i class="far fa-user nav-icon"></i><p>Manage Users</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Roles')) { ?>
                <li class="nav-item"><a href="roles.php" class="nav-link <?php echo ($current_page == 'roles.php') ? 'active' : ''; ?>" title="Manage Roles"><i class="far fa-user nav-icon"></i><p>Manage Roles</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Permission')) { ?>
                <li class="nav-item"><a href="permissions.php" class="nav-link <?php echo ($current_page == 'permissions.php') ? 'active' : ''; ?>" title="Manage Permission"><i class="far fa-user nav-icon"></i><p>Manage Permission</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Employer')) { ?>
                <li class="nav-item"><a href="employers.php" class="nav-link <?php echo ($current_page == 'employers.php') ? 'active' : ''; ?>" title="Manage Employers"><i class="far fa-user nav-icon"></i><p>Manage Employer</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Districts')) { ?>
                <li class="nav-item"><a href="districts.php" class="nav-link <?php echo ($current_page == 'districts.php') ? 'active' : ''; ?>" title="Manage Districts"><i class="far fa-user nav-icon"></i><p>Manage Districts</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Manage Talukas')) { ?>
                <li class="nav-item"><a href="talukas.php" class="nav-link <?php echo ($current_page == 'talukas.php') ? 'active' : ''; ?>" title="Manage Talukas"><i class="far fa-user nav-icon"></i><p>Manage Talukas</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Bulk Invoice Upload History')) { ?>
                <li class="nav-item"><a href="bulk-invoices-history.php" class="nav-link <?php echo ($current_page == 'bulk-invoices-history.php') ? 'active' : ''; ?>" title="Bulk Invoice Upload History"><i class="nav-icon fas fa-money-bill-wave"></i><p>Bulk Invoice Upload History</p></a></li>
            <?php } ?>
            <?php if (hasPermission('Reports')) { ?>
                <li class="nav-item"><a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" title="Reports"><i class="nav-icon fas fa-chart-line"></i><p>Reports</p></a></li>
            <?php } ?>
          <?php } ?>

          <li class="nav-item"><a href="logout.php" class="nav-link" title="Logout"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>