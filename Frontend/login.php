<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, roleid FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashedPassword, $roleid);

    if ($stmt->num_rows > 0 && $stmt->fetch()) {
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['roleid'] = $roleid;

            if ($roleid == 0) {
                // Redirect to admin dashboard
                header("Location: admin_dashboard.php");
            } else {
                // Redirect to user feed
                header("Location: home.php");
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found.";
    }
}
?>
