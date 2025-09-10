<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $phone = trim($_POST["phone"]);

    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";
            $created_at = date("Y-m-d H:i:s");

            $stmt = $conn->prepare("INSERT INTO users (role, username, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $role, $username, $email, $hashedPassword, $phone, $created_at);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: home.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register — RawVI</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <div class="container">
    <h2>Create Account</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="post" action="register.php">
      <input type="text" name="username" placeholder="Username" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="text" name="phone" placeholder="Contact Number" required />
      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
  </div>
</body>
</html>
