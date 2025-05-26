<?php
session_start();
if (isset($_SESSION["email"]) ) {
 header("Location : index.php");
 exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RawVI Admin Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2 class="logo">RawVI Admin</h2>
    <a href="#"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="#"><i class="fas fa-folder-open"></i> Media</a>
    <a href="#"><i class="fas fa-clock"></i> Pending</a>
    <a href="#"><i class="fas fa-users"></i> Users</a>
    <a href="#"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="#"><i class="fas fa-cogs"></i> Settings</a>
    <div class="admin-info">
      <p>Welcome, <?php echo $_SESSION['admin']; ?> 👋</p>
      <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

  <!-- Main Dashboard -->
  <div class="main-dashboard">
    <h1>📊 Dashboard Overview</h1>
    <div class="cards-container">
      <div class="card">
        <i class="fas fa-photo-video card-icon"></i>
        <h3>Total Media</h3>
        <p>120</p>
      </div>
      <div class="card">
        <i class="fas fa-users card-icon"></i>
        <h3>Total Users</h3>
        <p>45</p>
      </div>
      <div class="card">
        <i class="fas fa-upload card-icon"></i>
        <h3>Pending Uploads</h3>
        <p>6</p>
      </div>
      <div class="card">
        <i class="fas fa-headphones card-icon"></i>
        <h3>Audio Files</h3>
        <p>32</p>
      </div>
    </div>
  </div>
</body>
</html>
