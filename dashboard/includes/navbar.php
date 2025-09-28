<nav class="main-header navbar navbar-expand navbar-dark navbar-navy" style="background-color: #25255f;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>

    <!-- SEARCH FORM -->
    <!-- <form class="form-inline ml-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form> -->

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- My Account Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <span class="badge badge-warning ">
              <i class="far fa-user"></i> 
              <?php echo $_SESSION['user_name']; ?> 
              <i class="right fas fa-angle-down"></i>
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-header"><?php echo $_SESSION['user_email']; ?></span>
            <div class="dropdown-divider"></div>
            <a href="edit-profile.php" class="dropdown-item">
              <i class="fas fa-user mr-2"></i> Profile
            </a>
            <div class="dropdown-divider"></div>
            <a href="change-password.php" class="dropdown-item">
              <i class="fas fa-lock mr-2"></i> change Password
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item dropdown-footer"><i class="nav-icon fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </li>
    </ul>
  </nav>