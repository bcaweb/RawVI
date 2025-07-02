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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password_raw = $_POST['password'];
    $phone = mysqli_real_escape_string($con, $_POST['phone']);

    if (empty($role) || empty($username) || empty($email) || empty($password_raw) || empty($phone)) {
        $errors[] = "All fields are required.";
    }

    // Validate phone number: only digits, max length 10
    if (!preg_match('/^\d{1,10}$/', $phone)) {
        $errors[] = "Phone number must contain only digits and be up to 10 digits long.";
    }

    if (empty($errors)) {
        $password = password_hash($password_raw, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (role, username, email, password, phone, created_at)
                VALUES ('$role', '$username', '$email', '$password', '$phone', NOW())";

        if (mysqli_query($con, $sql)) {
            $success = "User added successfully!";
        } else {
            $errors[] = "Error: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User</title>
    <link rel="stylesheet" href="adduser.css"> <!-- Your CSS file -->
</head>
<body>

<div class="layout-container">
    <?php include('header.php'); ?>

    <div class="main-wrapper">
        <?php include('sidebar.php'); ?>

        <div class="main-container">
            <div class="card">
                <h2>Add New User</h2>

                <?php if (!empty($errors)): ?>
                    <div class="error-msg">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-msg">
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>

                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>

                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>

                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>

                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" maxlength="10" required>

                    <button type="submit">Add User</button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> Rawvi Real Estate. All rights reserved.
    </div>
</div>

</body>
</html>
