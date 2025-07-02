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

// Fetch dynamic categories and content types
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

    $fileUpdated = false;
    $fileName = $data['file'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $originalName = basename($_FILES['file']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_extensions)) {
            echo "<script>alert('Invalid file type.');</script>";
        } else {
            $newFileName = uniqid() . "." . $ext;
            $uploadDir = 'uploads/';
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                if ($data['file'] && file_exists($uploadDir . $data['file'])) {
                    unlink($uploadDir . $data['file']);
                }
                $fileName = $newFileName;
                $fileUpdated = true;
            } else {
                echo "<script>alert('Error uploading file.');</script>";
            }
        }
    }

    if ($name && $desc && $category && $type) {
        if ($fileUpdated) {
            $update_sql = "UPDATE content SET content_name = ?, content_desc = ?, category = ?, content_type = ?, file = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $desc, $category, $type, $fileName, $id);
        } else {
            $update_sql = "UPDATE content SET content_name = ?, content_desc = ?, category = ?, content_type = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $desc, $category, $type, $id);
        }
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
    <link rel="stylesheet" href="update.css">
</head>
<body>
<div class="container">
    <a href="view.php" class="back">&larr; Back to Library</a>
    <h2>Update Content</h2>

    <form method="POST" enctype="multipart/form-data">
        <label for="content_name">Content Name:</label>
        <input type="text" name="content_name" id="content_name" value="<?= htmlspecialchars($data['content_name']) ?>" required>

        <label for="content_desc">Description:</label>
        <textarea name="content_desc" id="content_desc" required><?= htmlspecialchars($data['content_desc']) ?></textarea>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <?php
            // Static categories
            $static_categories = ['Tech', 'Festival', 'People', 'Nature'];

            // Extract from DB
            $db_categories = array_column($categories, 'category');

            // Merge and remove duplicates
            $all_categories = array_unique(array_merge($static_categories, $db_categories), SORT_STRING);

            foreach ($all_categories as $cat):
                $selected = ($data['category'] === $cat) ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $selected ?>>
                    <?= htmlspecialchars($cat) ?>
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

        <!-- File Preview -->
        <?php
        $ext = strtolower(pathinfo($data['file'], PATHINFO_EXTENSION));
        $file_path = 'uploads/' . $data['file'];
        $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $is_video = in_array($ext, ['mp4', 'mov']);
        ?>
        <label>Current File:</label>
        <div class="current-file-preview">
            <?php if ($is_image): ?>
                <img src="<?= $file_path ?>" alt="Current Image" style="max-width: 300px;">
            <?php elseif ($is_video): ?>
                <video src="<?= $file_path ?>" controls style="max-width: 300px;"></video>
            <?php else: ?>
                <p>No preview available</p>
            <?php endif; ?>
        </div>

        <label for="file">Replace File (optional):</label>
        <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov">

        <button type="submit">Update Content</button>
    </form>
</div>
</body>
</html>
