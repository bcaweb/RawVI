<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$pin_id = intval($_POST['pin_id'] ?? 0);

if ($pin_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid pin id']);
    exit();
}

// Check if liked already
$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND pin_id = ?");
$stmt->bind_param("ii", $user_id, $pin_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Already liked, so unlike (toggle)
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND pin_id = ?");
    $stmt->bind_param("ii", $user_id, $pin_id);
    $stmt->execute();
    echo json_encode(['liked' => false]);
} else {
    // Like pin
    $stmt = $conn->prepare("INSERT INTO likes (user_id, pin_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $pin_id);
    $stmt->execute();
    echo json_encode(['liked' => true]);
}
?>
