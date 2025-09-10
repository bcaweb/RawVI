<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';
$user_id = $_SESSION['user_id'];

// Check if content ID is provided
if (!isset($_GET['id'])) {
    die("No content selected.");
}

$content_id = intval($_GET['id']);

// Fetch content details
$stmt = $conn->prepare("SELECT * FROM content WHERE id = ? AND uploaded_by = ?");
$stmt->bind_param("ii", $content_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Content not found or you don't have permission.");
}

$content = $result->fetch_assoc();

$error = '';
$success = '';

// Allowed extensions
$imageExts = ['jpg','jpeg','png','gif','webp'];
$videoExts = ['mp4','mov','webm'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['content_name']);
    $desc = trim($_POST['content_desc']);
    $content_type = $_POST['content_type'];
    $file_path = $content['file']; // default to current file

    // Handle new file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        // Validate file type matches content type
        if (($content_type === 'image' && !in_array($fileExtension, $imageExts)) ||
            ($content_type === 'video' && !in_array($fileExtension, $videoExts))) {
            $error = "❌ File type does not match the selected content type ($content_type).";
        } else {
            $uploadDir = 'Admin/uploads/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $newFileName = time() . '_' . basename($_FILES['file']['name']);
            $targetFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                // Delete old file
                if (file_exists($content['file'])) unlink($content['file']);
                $file_path = $targetFile;
            } else {
                $error = "❌ Failed to upload new file.";
            }
        }
    } else {
        // No new file uploaded, check current file matches selected content type
        $currentExt = strtolower(pathinfo($content['file'], PATHINFO_EXTENSION));
        if (($content_type === 'image' && !in_array($currentExt, $imageExts)) ||
            ($content_type === 'video' && !in_array($currentExt, $videoExts))) {
            $error = "❌ Selected content type does not match the current file.";
        }
    }

    // Update database if no errors
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE content SET content_name=?, content_desc=?, content_type=?, file=? WHERE id=? AND uploaded_by=?");
        $stmt->bind_param("ssssii", $name, $desc, $content_type, $file_path, $content_id, $user_id);

        if ($stmt->execute()) {
            $success = "✅ Content updated successfully!";
            // Update content array
            $content['content_name'] = $name;
            $content['content_desc'] = $desc;
            $content['content_type'] = $content_type;
            $content['file'] = $file_path;
        } else {
            $error = "❌ Failed to update content.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Upload - RawVI</title>
<link rel="stylesheet" href="updateuploads.css" />
</head>
<body>

<div class="container">
<h2>Edit Content</h2>

<?php if (!empty($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <label for="content_name">Content Name</label>
    <input type="text" name="content_name" id="content_name" value="<?= htmlspecialchars($content['content_name']) ?>" required>

    <label for="content_desc">Description</label>
    <textarea name="content_desc" id="content_desc" rows="4" required><?= htmlspecialchars($content['content_desc']) ?></textarea>

    <label for="content_type">Content Type</label>
    <select name="content_type" id="content_type" required>
        <option value="image" <?= $content['content_type'] === 'image' ? 'selected' : '' ?>>Image</option>
        <option value="video" <?= $content['content_type'] === 'video' ? 'selected' : '' ?>>Video</option>
    </select>

    <label for="file">Replace File (optional)</label>
    <input type="file" name="file" id="file" accept="image/*,video/*">

    <div class="current-file">
        <p>Current File:</p>
        <?php 
        $ext = strtolower(pathinfo($content['file'], PATHINFO_EXTENSION));
        if (in_array($ext, $imageExts)): ?>
            <img src="<?= $content['file'] ?>" alt="Current file">
        <?php elseif (in_array($ext, $videoExts)): ?>
            <video controls>
                <source src="<?= $content['file'] ?>" type="video/<?= $ext ?>">
            </video>
        <?php else: ?>
            <p>No preview available.</p>
        <?php endif; ?>
    </div>

    <button type="submit">Update Content</button>
    <a href="profile.php" class="btn-back">← Back to Profile</a>
</form>
</div>

</body>
</html>
