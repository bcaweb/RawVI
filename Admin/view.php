<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM content ORDER BY id DESC";
$result = mysqli_query($con, $sql);

$count_query = "SELECT 
                    COUNT(*) AS total, 
                    SUM(content_type='Image') AS images,
                    SUM(content_type='Video') AS videos,
                    COUNT(DISTINCT category) AS categories
                FROM content";
$counts = mysqli_fetch_assoc(mysqli_query($con, $count_query));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Content Library</title>
    <link rel="stylesheet" href="view.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style>
        .card {
            position: relative;
        }

        .card-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .action-icon {
            font-size: 20px;
            cursor: pointer;
            color: #555;
            transition: color 0.2s ease;
        }

        .action-icon:hover {
            color: #d32f2f;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <a href="dashboard.php" class="back-button material-icons">arrow_back</a>
        <h1>Content Library</h1>
        <p>Browse and manage uploaded media content.</p>
    </header>

    <div class="stats">
        <div>
            <span class="material-icons stats-icon">folder</span><br />
            <?= $counts['total'] ?? 0 ?><br />
            <span class="stats-label">Total Files</span>
        </div>
        <div>
            <span class="material-icons stats-icon">image</span><br />
            <?= $counts['images'] ?? 0 ?><br />
            <span class="stats-label">Images</span>
        </div>
        <div>
            <span class="material-icons stats-icon">videocam</span><br />
            <?= $counts['videos'] ?? 0 ?><br />
            <span class="stats-label">Videos</span>
        </div>
        <div>
            <span class="material-icons stats-icon">folder_open</span><br />
            <?= $counts['categories'] ?? 0 ?><br />
            <span class="stats-label">Categories</span>
        </div>
    </div>

    <div class="grid">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
            $file = htmlspecialchars($row['file']);
            $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $file_path = "uploads/" . $file;
            ?>
            <div class="card">
                <!-- Action Buttons -->
                <div class="card-actions">
                    <a href="update.php?id=<?= $row['id'] ?>" title="Update">
                        <span class="material-icons action-icon">edit</span>
                    </a>
                    <a href="delete.php?id=<?= $row['id'] ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this content?');">
                        <span class="material-icons action-icon">delete</span>
                    </a>
                </div>

                <div class="thumbnail-wrapper" onclick="window.open('<?= $file_path ?>', '_blank')">
                    <?php
                    if (file_exists($file_path)) {
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            echo "<img src='$file_path' alt='Preview of " . htmlspecialchars($row['content_name']) . "' loading='lazy'>";
                        } elseif (in_array($file_ext, ['mp4', 'mov'])) {
                            echo "<video src='$file_path' muted preload='metadata'></video>";
                        } else {
                            echo "<div class='file-icon material-icons'>insert_drive_file</div>";
                        }
                    } else {
                        echo "<div class='file-icon material-icons'>error_outline</div>";
                    }
                    ?>
                </div>
                <div class="card-body">
                    <h3><?= htmlspecialchars($row['content_name']) ?></h3>
                    <p><?= htmlspecialchars($row['content_desc']) ?></p>
                    <div class="tags">
                        <span class="tag"><?= htmlspecialchars($row['category']) ?></span>
                        <span class="tag"><?= htmlspecialchars($row['content_type']) ?></span>
                    </div>
                    <div class="meta">
                        <span class="material-icons meta-icon">person</span> <?= htmlspecialchars($row['uploaded_by']) ?><br/>
                        <span class="material-icons meta-icon">event</span> 
<?= isset($row['upload_date']) ? date("M d, Y h:i A", strtotime($row['upload_date'])) : "N/A" ?>

                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
