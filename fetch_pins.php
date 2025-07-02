<?php
include 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$tags = $_GET['tags'] ?? '';
$tags = trim($tags);

if ($tags) {
    $tagList = explode(',', $tags);
    $tagConditions = [];
    $params = [];
    $types = "";

    foreach ($tagList as $tag) {
        $tag = trim($tag);
        $tagConditions[] = "FIND_IN_SET(?, p.tags)";
        $params[] = $tag;
        $types .= "s";
    }

    $where = implode(" OR ", $tagConditions);

    $sql = "SELECT p.*, u.username,
        (SELECT COUNT(*) FROM likes l WHERE l.pin_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM likes l WHERE l.pin_id = p.id AND l.user_id = ?) AS user_liked
        FROM pins p
        JOIN users u ON p.user_id = u.id
        WHERE ($where)
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    // bind params dynamically
    $bind_params = array_merge([$types . "ii", ...$params, $user_id, $limit, $offset]);
    $stmt->bind_param($types . "iii", ...$params, $user_id, $limit, $offset);

} else {
    $sql = "SELECT p.*, u.username,
        (SELECT COUNT(*) FROM likes l WHERE l.pin_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM likes l WHERE l.pin_id = p.id AND l.user_id = ?) AS user_liked
        FROM pins p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$pins = [];
while ($row = $result->fetch_assoc()) {
    $pins[] = $row;
}

header('Content-Type: application/json');
echo json_encode($pins);
?>
