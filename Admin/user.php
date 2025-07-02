<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}

// Connect to database
$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch users
$query = "SELECT * FROM users";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Users List</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="users.css" />
</head>
<body>

<?php include('header.php'); ?>

<div class="layout-container"> <!-- FLEX CONTAINER -->

  <?php include('sidebar.php'); ?> <!-- SIDEBAR -->

  <div class="main-container"> <!-- RIGHT SIDE AREA -->

    <div class="header user-header">
      <h1>👥 Users List</h1>
      <a href="adduser.php" class="btn-add-user">
        <span class="material-icons">person_add</span> Add User
      </a>
    </div>

    <div class="main-content">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <div class="card">
        <h3>All Registered Users</h3>
        <table class="user-table">
          <thead>
            <tr>
              <th>S.N.</th>
              <th>Role</th>
              <th>Username</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Created At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sn = 1;
            while ($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
              <td data-label="S.N."><?php echo $sn++; ?></td>
              <td data-label="Role"><?php echo htmlspecialchars($row['role']); ?></td>
              <td data-label="Username"><?php echo htmlspecialchars($row['username']); ?></td>
              <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
              <td data-label="Phone"><?php echo htmlspecialchars($row['phone']); ?></td>
              <td data-label="Created At"><?php echo htmlspecialchars($row['created_at']); ?></td>
              <td data-label="Action">
                <div class="action-buttons">
                  <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                  <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div> <!-- End of main-container -->

</div> <!-- End of layout-container -->

</body>
</html>
