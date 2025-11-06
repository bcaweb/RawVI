<?php
// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}
?>
<!-- Font Awesome CDN for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Only for dropdown visibility, no other styling */
.dropdown-menu {
    display: none;
}
.dropdown-menu.show {
    display: block;
}
</style>

<div class="sidebar">
  <a href="dashboard.php" style="text-decoration:none;">
    <h2 class="logo">RawVI Admin</h2>
  </a>
  <a href="search.php"><i class="fas fa-compass"></i> Search</a>
  <!-- Dropdown -->
  <div class="dropdown">
    <button class="dropdown-toggle" onclick="toggleDropdown()" type="button">
      <i class="fas fa-folder-open"></i> Content <i class="fas fa-caret-down caret-icon"></i>
    </button>
    <div class="dropdown-menu" id="mediaDropdown">
      <a href="upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Content</a>
      <a href="view.php"><i class="fas fa-edit"></i> Manage Content</a>
    </div>
  </div>
  <a href="user.php"><i class="fas fa-users"></i> Users</a>
  <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
  

  <div class="admin-info">
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> 👋</p>
    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<script>
function toggleDropdown() {
    var menu = document.getElementById('mediaDropdown');
    menu.classList.toggle('show');
}

// Hide dropdown when clicking outside
document.addEventListener('click', function(event) {
    var dropdown = document.getElementById('mediaDropdown');
    var button = document.querySelector('.dropdown-toggle');
    if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>