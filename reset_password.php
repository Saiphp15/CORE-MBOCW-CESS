
<?php
session_start();
require_once './config/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // $sql = "SELECT email FROM users WHERE token = ?";
    $stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if (!$user) {
        $_SESSION['error'] = "Reset Password link expired.";
        header("Location: forgot_password.php");
        exit;
    }

} else {
    $_SESSION['error'] = "Reset Password link expired.";
    header("Location: forgot_password.php");
    exit;
}
?>

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MBOCWCESS Portal Reset Password</title>
  <link rel="icon" href="assets/img/favicon_io/favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      background: #f8f9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      display: flex;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      max-width: 900px;
      width: 100%;
    }

    .form-section {
      flex: 1;
      padding: 50px;
    }

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
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .image-section img {
      max-width: 100%;
      height: auto;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        max-width: 90%;
      }

      .image-section {
        padding: 10px;
      }

      .form-section {
        padding: 30px 20px;
      }
    }
  </style>
</head>

<body>
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
      <form action="reset.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        <!-- <input type="email" name="email" placeholder="Email Address" required /> -->
        <button type="submit">Reset Password</button>
      </form>
      <br>
       <div class="row">
            <div class="col-6">
                <p class="mb-1">
                    <a href="login.php">Login</a>
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
</body>

</html>