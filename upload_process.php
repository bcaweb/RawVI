<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $tags = $_POST['tags'];
    $user_id = $_SESSION['user_id'];

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir);

    $image_name = basename($_FILES['image']['name']);
    $target_file = $upload_dir . time() . "_" . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $valid_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $valid_types)) {
        echo "Only JPG, PNG, and GIF files are allowed.";
        exit();
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO pins (user_id, title, description, tags, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $title, $desc, $tags, $target_file);
        $stmt->execute();

        header("Location: home.php");
        exit();
    } else {
        echo "Failed to upload image.";
    }
} else {
    echo "Invalid request.";
}
