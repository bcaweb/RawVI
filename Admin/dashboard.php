<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check for upload success message
$upload_message = '';
if (isset($_SESSION['upload_success'])) {
    $upload_message = $_SESSION['upload_success'];
    unset($_SESSION['upload_success']); // Clear the message
}

// Check for any error messages
$error_message = '';
if (isset($_SESSION['upload_error'])) {
    $error_message = $_SESSION['upload_error'];
    unset($_SESSION['upload_error']); // Clear the message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RawVI Admin Dashboard</title>
  <link rel="stylesheet" href="dash.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* Sidebar dropdown */
    .dropdown-menu {
      display: none;
      flex-direction: column;
      padding-left: 20px;
    }
    .dropdown-menu.show {
      display: flex;
    }
    .dropdown-toggle {
      background: none;
      border: none;
      color: inherit;
      cursor: pointer;
      font-size: 1em;
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      text-align: left;
      padding: 10px 15px;
    }
    .dropdown .dropdown-menu a {
      padding: 8px 0;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      color: inherit;
      transition: background-color 0.3s ease;
    }
    .dropdown .dropdown-menu a:hover {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 5px;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      backdrop-filter: blur(3px);
    }

    .modal-content {
      background: #fff;
      margin: 5% auto;
      padding: 30px 25px;
      border-radius: 12px;
      width: 90%;
      max-width: 520px;
      position: relative;
      border-top: 8px solid rgb(121, 82, 179);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
      max-height: 90vh;
      overflow-y: auto;
    }

    .close-btn {
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
      color: #999;
      transition: color 0.3s ease;
    }
    .close-btn:hover {
      color: rgb(121, 82, 179);
    }

    #uploadForm input,
    #uploadForm select,
    #uploadForm textarea {
      width: 100%;
      margin-top: 14px;
      padding: 12px;
      border-radius: 6px;
      border: 1.5px solid #ddd;
      font-size: 0.95rem;
      transition: border 0.3s ease;
      box-sizing: border-box;
    }
    #uploadForm input:focus,
    #uploadForm select:focus,
    #uploadForm textarea:focus {
      outline: none;
      border-color: rgb(121, 82, 179);
    }

    #uploadForm button {
      width: 100%;
      margin-top: 18px;
      background-color: rgb(121, 82, 179);
      color: white;
      padding: 12px;
      font-size: 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    #uploadForm button:hover {
      background-color: rgb(101, 62, 159);
    }
    #uploadForm button:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }

    .modal-content h2 {
      margin-bottom: 15px;
      font-size: 1.5rem;
      color: rgb(121, 82, 179);
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }

    /* Success and Error message styles */
    .message {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 5px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      z-index: 10000;
      display: none;
      animation: slideIn 0.3s ease-out;
    }

    .success-message {
      background: #4CAF50;
      color: white;
    }

    .error-message {
      background: #f44336;
      color: white;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    /* File input styling */
    #uploadForm input[type="file"] {
      padding: 8px;
      border: 2px dashed #ddd;
      background: #f9f9f9;
      cursor: pointer;
    }
    #uploadForm input[type="file"]:hover {
      border-color: rgb(121, 82, 179);
      background: #f5f5f5;
    }

    /* Progress bar for upload */
    .progress-container {
      width: 100%;
      background-color: #f0f0f0;
      border-radius: 6px;
      margin-top: 10px;
      display: none;
    }

    .progress-bar {
      width: 0%;
      height: 6px;
      background-color: rgb(121, 82, 179);
      border-radius: 6px;
      transition: width 0.3s ease;
    }

    /* Form validation styles */
    .form-group {
      position: relative;
      margin-bottom: 10px;
    }

    .form-error {
      color: #f44336;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
    }

    .input-error {
      border-color: #f44336 !important;
    }

    /* Loading spinner */
    .spinner {
      border: 2px solid #f3f3f3;
      border-top: 2px solid rgb(121, 82, 179);
      border-radius: 50%;
      width: 16px;
      height: 16px;
      animation: spin 1s linear infinite;
      display: inline-block;
      margin-right: 8px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 20px 15px;
      }
      
      .message {
        right: 10px;
        left: 10px;
        width: auto;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2 class="logo">RawVI Admin</h2>
    <a href="#"><i class="fas fa-compass"></i> Search</a>

    <!-- Dropdown -->
    <div class="dropdown">
      <button class="dropdown-toggle" onclick="toggleDropdown()">
        <i class="fas fa-folder-open"></i> Content <i class="fas fa-caret-down caret-icon"></i>
      </button>
      <div class="dropdown-menu" id="mediaDropdown">
        <a href="upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Content</a>
        <a href="view.php"><i class="fas fa-eye"></i> View Content</a>
        <a href="update.php"><i class="fas fa-edit"></i> Update Content</a>
        <a href="delete.php"><i class="fas fa-trash-alt"></i> Delete Content</a>
      </div>
    </div>

    <a href="#"><i class="fas fa-clock"></i> Pending</a>
    <a href="#"><i class="fas fa-users"></i> Users</a>
    <a href="#"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="#"><i class="fas fa-cogs"></i> Settings</a>

    <div class="admin-info">
      <p>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?> 👋</p>
      <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

 

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
<script>
  function toggleDropdown() {
    document.getElementById("mediaDropdown").classList.toggle("show");
  }

  // Optional: Close dropdown if clicked outside
  window.onclick = function(event) {
    if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-toggle')) {
      const dropdowns = document.getElementsByClassName("dropdown-menu");
      for (let i = 0; i < dropdowns.length; i++) {
        if (dropdowns[i].classList.contains('show')) {
          dropdowns[i].classList.remove('show');
        }
      }
    }
  }
  </script>
