<?php
include 'db.php';
session_start();

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['roleid'] != 0) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_POST['user_id'];

    // Prevent deleting self
    if ($user_id == $_SESSION['user_id']) {
        die("You cannot delete your own admin account.");
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    header("Location: admin_dashboard.php");
}
?>
