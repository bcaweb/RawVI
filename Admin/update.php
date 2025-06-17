<?php
session_start();

$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Invalid content ID.");
}

// Fetch categories and content types
$category_query = mysqli_query($con, "SELECT DISTINCT category FROM content");
$content_type_query = mysqli_query($con, "SELECT DISTINCT content_type FROM content");
$categories = mysqli_fetch_all($category_query, MYSQLI_ASSOC);
$content_types = mysqli_fetch_all($content_type_query, MYSQLI_ASSOC);

// Fetch current data
$stmt = mysqli_prepare($con, "SELECT * FROM content WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Content not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['content_name'] ?? '');
    $desc = trim($_POST['content_desc'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $type = trim($_POST['content_type'] ?? '');

    if ($name && $desc && $category && $type) {
        $update_sql = "UPDATE content SET content_name = ?, content_desc = ?, category = ?, content_type = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $desc, $category, $type, $id);
        mysqli_stmt_execute($stmt);

        $_SESSION['update_success'] = "Content updated successfully!";
        header("Location: view.php");
        exit();
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Content</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="update.css">
</head>
<body>
    <div class="container">
        <a href="view.php" class="back">&larr; Back to Library</a>
        <h2>Update Content</h2>
        <form method="POST">
            <label for="content_name">Content Name:</label>
            <input type="text" name="content_name" id="content_name" value="<?= htmlspecialchars($data['content_name']) ?>" required>

            <label for="content_desc">Description:</label>
            <textarea name="content_desc" id="content_desc" required><?= htmlspecialchars($data['content_desc']) ?></textarea>

            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['category']) ?>" <?= ($data['category'] === $cat['category']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="content_type">Content Type:</label>
            <select name="content_type" id="content_type" required>
                <?php foreach ($content_types as $ct): ?>
                    <option value="<?= htmlspecialchars($ct['content_type']) ?>" <?= ($data['content_type'] === $ct['content_type']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ct['content_type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Update Content</button>
        </form>
    </div>
</body>
</html>
