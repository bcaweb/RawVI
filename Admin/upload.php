<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submit"])) {
    $upload_dir = __DIR__ . "/uploads/";
    $file = "";

    $content_name = mysqli_real_escape_string($con, $_POST["content_name"]);
    $category = mysqli_real_escape_string($con, $_POST["category"]);
    $content_type = mysqli_real_escape_string($con, $_POST["content_type"]);
    $content_desc = mysqli_real_escape_string($con, $_POST["content_desc"]);
    $uploaded_by = mysqli_real_escape_string($con, $_SESSION['email']);

    // Server-side word limit check
    $word_count = str_word_count(strip_tags($content_desc));
    if ($word_count > 100) {
        $error_message = "Description exceeds the 100-word limit. Please shorten it.";
    } else {
        if (!empty($_FILES['userfile']['name'])) {
            $file = basename($_FILES['userfile']['name']);
            $upload_file = $upload_dir . $file;

            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_file)) {
                $sql = "INSERT INTO content (content_name, category, content_type, content_desc, file, uploaded_by)
                        VALUES ('$content_name', '$category', '$content_type', '$content_desc', '$file', '$uploaded_by')";

                if (mysqli_query($con, $sql)) {
                    $success_message = "🎉 File uploaded successfully! <a href='view.php'>Go to Content Management</a>";
                } else {
                    $error_message = "Database error: " . mysqli_error($con);
                }
            } else {
                $error_message = "Error uploading file: " . $_FILES['userfile']['error'];
            }
        } else {
            $error_message = "No file selected.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Content</title>
    <link rel="stylesheet" href="upload.css">
</head>
<body>
<div class="layout">
    <div class="sidebar">
        <?php include('sidebar.php'); ?>
    </div>

    <div class="main">
        <?php include('header.php'); ?>

        <div class="content">
            <?php if (isset($success_message)): ?>
                <div class="success-box"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="error-box"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="container">
                <a href="dashboard.php" class="back">&larr; Back to Dashboard</a>
                <h3>Upload New Content</h3>
                <form action="" method="post" enctype="multipart/form-data">
                    <label for="content_name">Content Name</label>
                    <input type="text" id="content_name" name="content_name" required>

                    <label for="category">Select Category</label>
                    <select id="category" name="category" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="People">People</option>
                        <option value="Tech">Tech</option>
                        <option value="Nature">Nature</option>
                        <option value="Festival">Festival</option>
                    </select>

                    <label for="content_type">Content Type</label>
                    <select id="content_type" name="content_type" required>
                        <option value="" disabled selected>Content Type</option>
                        <option value="Image">Image</option>
                        <option value="Video">Video</option>
                    </select>

                    <label for="content_desc">Content Description</label>
                    <textarea id="content_desc" name="content_desc" rows="4" oninput="enforceWordLimit()" required></textarea>
                    <p id="word-warning" style="color: #6a1b9a; font-weight: bold;">Words: 0/100</p>

                    <label for="userfile">Choose File</label>
                    <input type="file" id="userfile" name="userfile" accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov" required>
                    <p>Supported formats: JPG, JPEG, PNG, GIF, WEBP, MP4, MOV (Max: 50MB)</p>

                    <button type="submit" name="submit" id="submit-btn">Upload Content</button>
                </form>
            </div>
        </div>

        <?php include('footer.php'); ?>
    </div>
</div>

<script>
function countWords(text) {
    return text.trim().split(/\s+/).filter(word => word.length > 0).length;
}

function enforceWordLimit() {
    const textarea = document.getElementById("content_desc");
    const wordCount = countWords(textarea.value);
    const warning = document.getElementById("word-warning");
    const submitBtn = document.getElementById("submit-btn");

    if (wordCount > 100) {
        warning.textContent = `⚠️ Word limit exceeded: ${wordCount}/100 words.`;
        warning.style.color = "red";
        submitBtn.disabled = true;
    } else {
        warning.textContent = `Words: ${wordCount}/100`;
        warning.style.color = "#6a1b9a";
        submitBtn.disabled = false;
    }
}
</script>
</body>
</html>
