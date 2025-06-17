<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$id = $_GET['id'] ?? null;
if ($id) {
    // Optionally remove file too
    $result = mysqli_query($con, "SELECT file FROM content WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    $file_path = "uploads/" . $row['file'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Delete from database
    mysqli_query($con, "DELETE FROM content WHERE id = $id");
}

header("Location: view.php");
exit();
