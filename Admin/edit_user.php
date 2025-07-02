<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$errors = [];
$success = "";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($con, $query);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        die("User not found.");
    }
} else {
    die("Invalid user ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);

    if (empty($role) || empty($username) || empty($email) || empty($phone)) {
        $errors[] = "All fields are required.";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (empty($errors)) {
        $update_sql = "UPDATE users SET 
                       role='$role', 
                       username='$username', 
                       email='$email', 
                       phone='$phone' 
                       WHERE id=$user_id";

        if (mysqli_query($con, $update_sql)) {
            header("Location: user.php?success=1");
            exit();
        } else {
            $errors[] = "Update failed: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="edituser.css">
</head>
<body>

<?php include('header.php'); ?>

<div class="layout-container">
    <?php include('sidebar.php'); ?>

    <div class="main-container">
        <div class="card">
            <h2>Edit User</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-msg">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                </select>

                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

                <button type="submit">Update User</button>
            </form>
        </div>
    </div>
</div>

<!-- Footer directly inside this file -->
<div class="footer">
    &copy; <?php echo date("Y"); ?> Rawvi Real Estate. All rights reserved.
</div>

</body>
</html>
