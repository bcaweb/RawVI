<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent deleting yourself
    $current_email = $_SESSION['email'];
    $self_check = mysqli_query($con, "SELECT id FROM users WHERE id = $user_id AND email = '$current_email'");
    if (mysqli_num_rows($self_check) > 0) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header("Location: user.php");
        exit();
    }

    // Delete the user
    $delete_query = "DELETE FROM users WHERE id = $user_id";

    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user: " . mysqli_error($con);
    }

    header("Location: user.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: user.php");
    exit();
}
?>
