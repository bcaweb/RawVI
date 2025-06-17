<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // redirect if not logged in
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['pin_image']) && $_FILES['pin_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pin_image']['tmp_name'];
        $fileName = $_FILES['pin_image']['name'];
        $fileSize = $_FILES['pin_image']['size'];
        $fileType = $_FILES['pin_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/uploads/';
            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // TODO: Save to DB, example:
                // $pdo->prepare("INSERT INTO pins (user_id, title, image_url) VALUES (?, ?, ?)")
                //     ->execute([$_SESSION['user_id'], $_POST['title'], "uploads/" . $newFileName]);

                header("Location: index.php?upload=success");
                exit();
            } else {
                $errorMsg = "Error moving the uploaded file.";
            }
        } else {
            $errorMsg = "Upload failed. Allowed types: " . implode(", ", $allowedExts);
        }
    } else {
        $errorMsg = "No file uploaded or upload error.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Pin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body 
    
    {
      margin: 0;
      padding: 0;
      height: 100vh;
      background: url('images/upload.jpg') no-repeat center center fixed;
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

    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 28px;
    }

    label {
      font-weight: 500;
      display: block;
      margin-top: 15px;
      margin-bottom: 5px;
    }

    input[type="text"], input[type="file"] {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      outline: none;
      margin-bottom: 15px;
    }

    input[type="file"]::file-selector-button {
      padding: 10px 15px;
      border-radius: 6px;
      border: none;
      background: #ffffff33;
      color: white;
      cursor: pointer;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #ff3c5f;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    button:hover {
      background-color: #ff2a4e;
    }
    
  </style>
</head>
<body>

  <div class="upload-container">
    <h2>Upload a Pin</h2>
   <form action="upload_pins.php" method="post" enctype="multipart/form-data">
  <label for="title">Title</label>
  <input type="text" name="title" id="title" placeholder="Enter a title" required>

  <label for="image">Choose Image</label>
  <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)" required>

  <!-- 🖼️ Image Preview Box -->
  <div id="preview-box" style="display:none; margin: 15px 0;">
    <p style="margin-bottom: 10px;">Preview:</p>
    <img id="preview-img" src="" alt="Preview" style="max-width: 100%; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.3);">
  </div>

  <button type="submit">Upload</button>
  
</form>
<a href="javascript:history.back()" style="position: fixed; top: 10px; left: 10px; font-size: 16px; color:rgb(255, 255, 255); text-decoration: none; cursor: pointer;">← Back</a>

  </div>
<script>
  function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
      const previewBox = document.getElementById('preview-box');
      const previewImg = document.getElementById('preview-img');
      previewImg.src = URL.createObjectURL(file);
      previewBox.style.display = 'block';
    }
  }
</script>

</body>
</html>
