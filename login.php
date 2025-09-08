<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MBOCWCESS Portal Login</title>
  <link rel="icon" href="assets/img/favicon_io/favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      display: flex;
<<<<<<< HEAD
=======
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
>>>>>>> a928542b532cc859ec2cbbeb5c1419e2383cee76
      overflow: hidden;
    }

    /* Left Panel */
    .left-panel {
      flex: 1.2;
      display: flex;
      width: 65%;
    }

<<<<<<< HEAD
    /* Right Panel */
    .right-panel {
      flex: 0.8;
      background: #f4f6f9;
=======
    .form-section h1 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 10px;
      color: #343a40;
    }

    .form-section p {
      font-size: 14px;
      color: #666;
      margin-bottom: 30px;
    }

    .form-section input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    .form-section button {
      width: 100%;
      padding: 12px;
      border: none;
      background: linear-gradient(to right, #007bff, #339af0);
      color: white;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .image-section {
      flex: 1;
      background: #e9ecef;
>>>>>>> a928542b532cc859ec2cbbeb5c1419e2383cee76
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      width: 33%;
    }
    .login-container {
      width: 100%;
      max-width: 480px;
    }

    /* Card */
    .login-form {
      background: #fff;
      padding: 35px;
      border-radius: 18px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .login-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .login-header img {
      width: 90%;
      height: 300px;
      margin-bottom: 12px;
    }
    .login-header h2 {
      font-size: 20px;
      font-weight: 700;
      color: #333;
    }

    /* Input fields with icons */
    .input-group-text {
      background: #f8f9fa;
      border-radius: 10px 0 0 10px;
      border: 1px solid #ced4da;
    }
    .form-control {
      border-radius: 0 10px 10px 0;
      padding: 12px;
    }

    /* Button */
    .btn-login {
      width: 100%;
      background: linear-gradient(to right, #007bff, #339af0);
      border: none;
      border-radius: 10px;
      padding: 12px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0,123,255,0.35);
    }

    /* Links */
    .extra-links {
      margin-top: 15px;
      text-align: center;
    }
    .extra-links a {
      font-size: 14px;
      color: #007bff;
      text-decoration: none;
    }
    .extra-links a:hover {
      text-decoration: underline;
    }

    @media (max-width: 992px) {
      body {
        flex-direction: column;
      }
      .left-panel {
        height: 200px;
        flex: unset;
        padding: 20px;
      }
      .right-panel {
        flex: unset;
        width: 100%;
        height: auto;
      }
    }
  </style>
</head>

<body>
<<<<<<< HEAD
  <!-- Left side with overlay text -->
  <div class="left-panel">
    <img src="assets/img/mbocwwb.png" alt="">
  </div>

  <!-- Right side login form -->
  <div class="right-panel">
    <div class="login-container">
      <div class="login-form">
        <div class="login-header">
          <img src="assets/img/mbocw-img.png" alt="Logo">
          <h2>MBOCWB CESS Portal</h2>
        </div>
        <form action="auth.php" method="post">
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
          </div>
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
          </div>
          <button type="submit" class="btn-login">Log in</button>
        </form>
        <div class="extra-links">
          <a href="forgot-password.php">Forgot your password?</a><br><br>
          <a href="index.php">Back to Home</a>
        </div>
      </div>
    </div>
  </div>
=======
  <div class="container">
    <div class="form-section">
      <h1>MBOCWCESS Portal</h1>
      <p>MBOCW CESS</p>
      <?php
      if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
      }
      ?>
      <!-- Error Message -->
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                        unset($_SESSION['error']); ?></div>
      <?php endif; ?>
      <form action="auth.php" method="post">
        <input type="email" name="email" placeholder="Email Address" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Log in</button>
      </form>
      <br>
       <div class="row">
            <div class="col-6">
                <p class="mb-1">
                    <a href="forgot_password.php">I forgot my password</a>
                </p>
            </div>
            <div class="col-6">
              <p class="mb-1">
                <a href="index.php">Click here to Home Page</a>
              </p>
            </div>
        </div>
      
    </div>
    <div class="image-section">
      <img src="assets/img/mbocwcess-login.png" alt="Maharashtra Building and Other Construction Workers Welfare Board Logo" />
    </div>
  </div>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> a928542b532cc859ec2cbbeb5c1419e2383cee76
</body>

</html>