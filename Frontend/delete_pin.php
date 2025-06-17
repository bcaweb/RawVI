<?php
include 'db.php';
session_start();

// Only admin can delete pins
if (!isset($_SESSION['user_id']) || $_SESSION['roleid'] != 0) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin_id = $_POST['pin_id'];

    // Optionally delete image file (use with caution)
    $img_query = $conn->query("SELECT image_path FROM pins WHERE id=$pin_id");
    if ($img = $img_query->fetch_assoc()) {
        if (file_exists($img['image_path'])) {
            unlink($img['image_path']);
        }
    }

    $stmt = $conn->prepare("DELETE FROM pins WHERE id = ?");
    $stmt->bind_param("i", $pin_id);
    $stmt->execute();

    header("Location: admin_dashboard.php");
}
?>
