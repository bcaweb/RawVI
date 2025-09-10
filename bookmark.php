<?php
// bookmark.php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

require 'db.php';

$userId = $_SESSION['user_id'];
$contentId = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;

if ($contentId <= 0) {
    http_response_code(400);
    echo "Invalid content ID";
    exit();
}

$query = "INSERT IGNORE INTO bookmark (user_id, content_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $contentId);

if ($stmt->execute()) {
    echo "Bookmarked successfully";
} else {
    http_response_code(500);
    echo "Failed to bookmark";
}
?>
