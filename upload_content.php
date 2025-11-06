<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php'; // Uses $conn (MySQLi)

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Allowed extensions
        $allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedVideoExts = ['mp4', 'mov', 'webm'];
        $allowedOtherExts = ['pdf', 'mp3', 'docx'];

        $contentType = $_POST['content_type'];

        // Validate file extension against content type
        if ($contentType === 'image' && !in_array($fileExtension, $allowedImageExts)) {
            $errorMsg = "❌ You selected 'Image' but uploaded a non-image file.";
        } elseif ($contentType === 'video' && !in_array($fileExtension, $allowedVideoExts)) {
            $errorMsg = "❌ You selected 'Video' but uploaded a non-video file.";
        } elseif (!in_array($fileExtension, array_merge($allowedImageExts, $allowedVideoExts, $allowedOtherExts))) {
            $errorMsg = "❌ Invalid file type. Allowed: " . implode(", ", array_merge($allowedImageExts, $allowedVideoExts, $allowedOtherExts));
        } else {
            // Move the file
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/Admin/uploads/';
            $destPath = $uploadDir . $newFileName;

            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $filePath = 'Admin/uploads/' . $newFileName;
                $stmt = $conn->prepare("INSERT INTO content
                    (content_name, category, content_type, content_desc, file, uploaded_by)
                    VALUES (?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "sssssi",
                    $_POST['content_name'],
                    $_POST['category'],
                    $_POST['content_type'],
                    $_POST['content_desc'],
                    $filePath,
                    $_SESSION['user_id']
                );

                if ($stmt->execute()) {
                    $_SESSION['upload_success'] = "✅ Content uploaded successfully!";
                    header("Location: home.php");
                    exit();
                } else {
                    $errorMsg = "❌ Database error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $errorMsg = "❌ Failed to move uploaded file.";
            }
        }

    } else {
        $errorMsg = "❌ No file uploaded or upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Content</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; font-family: 'Poppins', sans-serif; }

body {
  margin: 0;
  padding: 0;
  height: 100vh;
  background: url('images/Abstract 1.jpg') no-repeat center center fixed;
  background-size: cover;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  border-radius: 16px;
  padding: 40px;
  width: 100%;
  max-width: 500px;
  color: white;
}

h2 { text-align: center; margin-bottom: 20px; }

label { display: block; margin-top: 15px; }

input, textarea, select {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 10px;
  margin-top: 5px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  margin-bottom: 15px;
}

input[type="file"]::file-selector-button {
  background: #fff3;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}

button {
  width: 100%;
  padding: 12px;
  background: #ff3c5f;
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.3s;
}

button:hover { background: #ff1f47; }

.error { color: pink; margin-bottom: 10px; text-align: center; }

small { display:block; margin-bottom:10px; color:#ffd700; }
</style>
</head>
<body>

<div class="upload-container">
  <h2>Upload Content</h2>

  <?php if (!empty($errorMsg)): ?>
    <div class="error"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <form action="upload_content.php" method="post" enctype="multipart/form-data">
    <label>Content Name</label>
    <input type="text" name="content_name" required>

    <label>Category</label>
    <select name="category" required>
      <option value="">-- Select --</option>
      <option value="People">People</option>
      <option value="Tech">Tech</option>
      <option value="Festival">Festival</option>
      <option value="Nature">Nature</option>
       <option value="Geography">Geography</option>
    </select>

    <label>Content Type</label>
    <select name="content_type" id="content_type" required>
      <option value="">-- Select --</option>
      <option value="image">Image</option>
      <option value="video">Video</option>
    </select>

    <label>Description (max 50 words)</label>
    <textarea name="content_desc" id="content_desc" rows="4" required></textarea>
    <small id="wordCount">0 / 50 words</small>

    <label>Choose File</label>
    <input type="file" name="file" id="file_input" accept="image/*,video/*,audio/*,.pdf,.docx" required>

    <button type="submit">Upload</button>
  </form>
</div>

<script>
// Word count limit
const textarea = document.getElementById('content_desc');
const wordCountDisplay = document.getElementById('wordCount');
const maxWords = 50;

textarea.addEventListener('input', () => {
    let words = textarea.value.match(/\S+/g) || [];
    if (words.length > maxWords) {
        textarea.value = words.slice(0, maxWords).join(" ");
        words = textarea.value.match(/\S+/g) || [];
    }
    wordCountDisplay.textContent = `${words.length} / ${maxWords} words`;
});

// Front-end content type validation
const fileInput = document.getElementById('file_input');
const contentTypeSelect = document.getElementById('content_type');

fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (!file) return;

    const fileExt = file.name.split('.').pop().toLowerCase();
    const selectedType = contentTypeSelect.value;

    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const videoExts = ['mp4', 'mov', 'webm'];

    if (selectedType === 'image' && !imageExts.includes(fileExt)) {
        alert("❌ You selected 'Image' content type. Please upload a image file.");
        fileInput.value = '';
    } else if (selectedType === 'video' && !videoExts.includes(fileExt)) {
        alert("❌ You selected 'Video' content type. Please upload a video file.");
        fileInput.value = '';
    }
});
</script>

</body>
</html>
