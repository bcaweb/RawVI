<?php
session_start();
$loginError = '';

if (isset($_POST['login'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $con = mysqli_connect("localhost", "root", "", "rawvi");

  if ($con) {
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $res = mysqli_query($con, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
      $_SESSION['email'] = $email;
      header("Location: https://localhost/RawVi/Admin/dashboard.php");
      exit();
    } else {
      $loginError = "❌ Incorrect email or password.";
    }
  } else {
    $loginError = "⚠️ Database connection failed.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RawVi</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="container">
    <div class="login-box">
     
      <p class="admin-title">ADMIN</p>

      <?php if ($loginError != ''): ?>
        <div class="error"><?php echo $loginError; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="input-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
        <div class="btn-group">
          <button type="submit" name="login">Sign In</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
