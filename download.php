<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['content_id'])) {
    http_response_code(400);
    echo "Unauthorized or missing data";
    exit();
}

$user_id = $_SESSION['user_id'];
$content_id = intval($_POST['content_id']);

$stmt = $conn->prepare("INSERT INTO download (user_id, content_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $content_id);
$stmt->execute();
$stmt->close();

echo "Logged";
?>
