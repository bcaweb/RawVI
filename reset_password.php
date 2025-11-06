<?php
session_start();

// Ensure user came from OTP verification
if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit;
}

$message = "";

// Database connection
$conn = new mysqli("localhost", "root", "", "rawvi");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST['password']);

    if (empty($password)) {
        $message = "❌ Please enter a new password.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $email = $_SESSION['email'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);

        if ($stmt->execute()) {
            $message = "✅ Password reset successful! Redirecting to login page...";
            session_unset();
            // Redirect to login.php after 3 seconds
            echo '<script>setTimeout(function(){ window.location="login.php"; }, 3000);</script>';
        } else {
            $message = "❌ Error updating password. Please try again.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Set New Password</h2>
        <form action="reset_password.php" method="post">
            <input type="password" name="password" placeholder="Enter new password" required>
            <button type="submit">Reset Password</button>
        </form>
        <div class="message">
            <?php if (!empty($message)) echo htmlspecialchars($message); ?>
        </div>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>
