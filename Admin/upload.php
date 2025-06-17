<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Connect to the database
$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submit"])) {
    $upload_dir = __DIR__ . "/uploads/";
    $file = "";

    // Sanitize form inputs
    $content_name = mysqli_real_escape_string($con, $_POST["content_name"]);
    $category = mysqli_real_escape_string($con, $_POST["category"]);
    $content_type = mysqli_real_escape_string($con, $_POST["content_type"]);
    $content_desc = mysqli_real_escape_string($con, $_POST["content_desc"]);
    $uploaded_by = mysqli_real_escape_string($con, $_SESSION['email']);

    // Handle file upload
    if (!empty($_FILES['userfile']['name'])) {
        $file = basename($_FILES['userfile']['name']);
        $upload_file = $upload_dir . $file;

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_file)) {
            // File uploaded, now insert into DB
            $sql = "INSERT INTO content (content_name, category, content_type, content_desc, file, uploaded_by)
                    VALUES ('$content_name', '$category', '$content_type', '$content_desc', '$file', '$uploaded_by')";

            if (mysqli_query($con, $sql)) {
    $success_message = "🎉 File uploaded successfully! <a href='dashboard.php'>Click here to go to Dashboard</a>";
} else {
    $error_message = "Database error: " . mysqli_error($con);
}

        } else {
            echo "Error uploading file: " . $_FILES['userfile']['error'];
        }
    } else {
        echo "No file selected.";
    }
}

?>
<?php if (isset($success_message)): ?>
    <div style="margin-top: 20px; padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
        <?php echo $success_message; ?>
    </div>
<?php elseif (isset($error_message)): ?>
    <div style="margin-top: 20px; padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload New Content</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="upload.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .modal {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        .modal-content h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
        }
        input[type="file"] {
            margin-bottom: 15px;
            padding: 8px 0;
        }
        p {
            font-size: 0.9em;
            color: #777;
            margin-top: -10px;
            margin-bottom: 20px;
        }
        .upload-button {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .upload-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="modal">
        <div class="modal-content">
            <h2>Upload New Content</h2>
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
                <textarea id="content_desc" name="content_desc" rows="4" required></textarea>

                <label for="userfile">Choose File</label>
                <input type="file" id="userfile" name="userfile" accept=".jpg, .jpeg, .png, .gif, .webp, .mp4, .mov" required>
                <p>Supported formats: JPG, JPEG, PNG, GIF, WEBP, MP4, MOV (Max: 50MB)</p>

                <button type="submit" name="submit" class="upload-button">Upload Content</button>
            </form>
        </div>
    </div>
</body>
</html>