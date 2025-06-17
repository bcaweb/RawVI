<?php
include 'db.php';
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>RawVI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Welcome to RawVI</h2>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Hello, <?= $_SESSION['username']; ?> | <a href="logout.php">Logout</a></p>
        <form action="upload_pins.php" method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Pin title" required>
            <textarea name="description" placeholder="Pin description"></textarea>
            <input type="file" name="image" required>
            <button type="submit">Upload</button>
        </form>
    <?php else: ?>
        <a href="login.html">Login</a> | <a href="register.html">Register</a>
    <?php endif; ?>

    <div class="pin-container">
        <?php
        $result = $conn->query("SELECT pins.*, users.username FROM pins JOIN users ON pins.user_id = users.id ORDER BY pins.created_at DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<div class='pin'>";
            echo "<img src='uploads/{$row['image_path']}' alt='Pin'>";
            echo "<h3>{$row['title']}</h3>";
            echo "<p>{$row['description']}</p>";
            echo "<small>by {$row['username']}</small>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
