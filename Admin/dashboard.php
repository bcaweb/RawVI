<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}

// Database connection
$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch total users
$user_query = "SELECT COUNT(*) AS total_users FROM users";
$result = mysqli_query($con, $user_query);
$row = mysqli_fetch_assoc($result);
$total_users = $row['total_users'];

// Fetch total content uploaded dynamically
$content_query = "SELECT COUNT(*) AS total_content FROM content";
$content_result = mysqli_query($con, $content_query);
$content_row = mysqli_fetch_assoc($content_result);
$total_content = $content_row['total_content'];
?>

<?php include('header.php'); ?>
<div class="layout-container"> <!-- FLEX CONTAINER -->

  <?php include('sidebar.php'); ?> <!-- SIDEBAR -->

  <div class="main-container"> <!-- RIGHT SIDE AREA -->
    <div class="header">
      <h1>📊 Dashboard Overview</h1>
    </div>

    <div class="main-dashboard">
      <div class="cards-container">

        <!-- 🔢 Dynamic Total Users -->
        <div class="card">
          <div class="card-icon"><i class="fas fa-users"></i></div>
          <h3>Total Users</h3>
          <p><?php echo $total_users; ?></p>
        </div>

        <!-- 🔄 Dynamic Content Uploaded -->
        <div class="card">
          <div class="card-icon"><i class="fas fa-cloud-upload-alt"></i></div>
          <h3>Content Uploaded</h3>
          <p><?php echo $total_content; ?></p>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-file-alt"></i></div>
          <h3>Reports</h3>
          <p>📕</p>
        </div>

      </div>
    </div>

  <?php include('footer.php'); ?>
  </div>
</div>
