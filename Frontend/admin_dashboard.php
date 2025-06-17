<?php
include 'db.php';
session_start();

// Access control: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['roleid'] != 0) {
    header("Location: login.html");
    exit();
}

// Analytics queries
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalAdmins = $conn->query("SELECT COUNT(*) AS total FROM users WHERE roleid=0")->fetch_assoc()['total'];
$totalPins = $conn->query("SELECT COUNT(*) AS total FROM pins")->fetch_assoc()['total'];

// Filter handling
$filter_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$filter_date = isset($_GET['date']) ? $_GET['date'] : null;

// Query pins with optional filters
$pin_query = "SELECT pins.*, users.username FROM pins 
              JOIN users ON pins.user_id = users.id";
$where = [];

if ($filter_user_id) {
    $where[] = "user_id = $filter_user_id";
}
if ($filter_date) {
    $where[] = "DATE(pins.created_at) = '$filter_date'";
}
if (!empty($where)) {
    $pin_query .= " WHERE " . implode(" AND ", $where);
}
$pin_query .= " ORDER BY pins.created_at DESC";
$pins = $conn->query($pin_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Pinterest Clone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>🛠 Admin Dashboard</h2>
    <p>Hello Admin, <?= $_SESSION['username']; ?> | <a href="logout.php">Logout</a></p>

    <!-- 🔍 Analytics -->
    <h3>📊 Analytics</h3>
    <ul>
        <li>Total Users: <?= $totalUsers ?></li>
        <li>Total Admins: <?= $totalAdmins ?></li>
        <li>Total Pins: <?= $totalPins ?></li>
    </ul>

    <!-- 👤 User Management -->
    <h3>👥 Manage Users</h3>
    <table border="1" cellpadding="10">
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>
        <?php
        $users = $conn->query("SELECT * FROM users");
        while ($u = $users->fetch_assoc()) {
            $role = $u['roleid'] == 0 ? "Admin" : "User";
            echo "<tr>
                    <td>{$u['id']}</td>
                    <td>{$u['username']}</td>
                    <td>{$u['email']}</td>
                    <td>$role</td>
                    <td>
                        <form method='post' action='delete_user.php' onsubmit='return confirm(\"Delete this user?\")'>
                            <input type='hidden' name='user_id' value='{$u['id']}'>
                            <button type='submit'>Delete</button>
                        </form>
                    </td>
                  </tr>";
        }
        ?>
    </table>

    <!-- 📁 Filter Pins -->
    <h3>📂 Filter Pins</h3>
    <form method="get" style="margin-bottom: 20px;">
        <label>User ID: <input type="number" name="user_id" value="<?= $filter_user_id ?>"></label>
        <label>Date: <input type="date" name="date" value="<?= $filter_date ?>"></label>
        <button type="submit">Apply Filters</button>
        <a href="admin_dashboard.php">Reset</a>
    </form>

    <!-- 📌 All Pins -->
    <h3>📌 All Pins</h3>
    <div class="pin-container">
        <?php
        while ($pin = $pins->fetch_assoc()) {
            echo "<div class='pin'>
                    <img src='{$pin['image_path']}' alt='Pin'>
                    <h3>{$pin['title']}</h3>
                    <p>{$pin['description']}</p>
                    <small>By {$pin['username']} on " . date("Y-m-d", strtotime($pin['created_at'])) . "</small>
                    <form method='post' action='delete_pin.php' onsubmit='return confirm(\"Delete this pin?\")'>
                        <input type='hidden' name='pin_id' value='{$pin['id']}'>
                        <button type='submit'>Delete</button>
                    </form>
                  </div>";
        }
        ?>
    </div>
</body>
</html>
