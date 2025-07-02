<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // e.g. "admin" or "user"

            // Optional: log last login time
            // $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            // $update->bind_param("i", $row['id']);
            // $update->execute();

            if (strtolower($row['role']) === 'admin') {
                header("Location: Admin/dashboard.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            echo "❌ Invalid password.";
        }
    } else {
        echo "❌ No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p><a href="forgot_password.html">Forgot Password?</a></p>
        <p>Don't have an account? <a href="register.php">Create One</a></p>
    </div>
</body>
</html>
