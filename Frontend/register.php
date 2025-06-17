<?php
include 'db.php'; // ✅ your database connection
session_start();

// Handle POST only
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        die("Please fill in all fields.");
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "⚠ Email is already registered.";
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Default role: 1 = normal user (0 = admin)
    $roleid = 1;

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, roleid) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $hashedPassword, $roleid);

    if ($stmt->execute()) {
        // Auto login and redirect to homepage
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['roleid'] = $roleid;
        header("Location: home.php"); // change to your home page
        exit();
    } else {
        echo "❌ Something went wrong during registration.";
    }
} else {
    echo "Invalid request.";
}
?>
