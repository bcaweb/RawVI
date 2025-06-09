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
  <link rel="stylesheet" href="style.css" />
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
    <a href="#"><i class="fas fa-compass"></i> Explore</a>

    <!-- Dropdown -->
    <div class="dropdown">
      <button class="dropdown-toggle" onclick="toggleDropdown()">
        <i class="fas fa-folder-open"></i> Content <i class="fas fa-caret-down caret-icon"></i>
      </button>
      <div class="dropdown-menu" id="mediaDropdown">
        <a href="#" onclick="openUploadModal()"><i class="fas fa-cloud-upload-alt"></i> Upload Content</a>
        <a href="view.php"><i class="fas fa-eye"></i> View Content</a>
        <a href="update-list.php"><i class="fas fa-edit"></i> Update Content</a>
        <a href="delete-list.php"><i class="fas fa-trash-alt"></i> Delete Content</a>
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

  <!-- Upload Modal -->
  <div id="uploadModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeUploadModal()">&times;</span>
      <h2><i class="fas fa-upload"></i> Upload New Content</h2>
      
      <form id="uploadForm" method="post" action="upload-handler.php" enctype="multipart/form-data">
        <div class="form-group">
          <input type="text" name="content_name" id="content_name" placeholder="Content Name" required />
          <div class="form-error" id="content_name_error">Content name is required</div>
        </div>

        <div class="form-group">
          <select name="category" id="category" required>
            <option value="" disabled selected>Select Category</option>
            <option value="Nature">Nature</option>
            <option value="Tech">Tech</option>
            <option value="People">People</option>
            <option value="Animals">Animals</option>
            <option value="Architecture">Architecture</option>
            <option value="Sports">Sports</option>
            <option value="Food">Food</option>
            <option value="Travel">Travel</option>
            <option value="Art">Art</option>
            <option value="Music">Music</option>
          </select>
          <div class="form-error" id="category_error">Please select a category</div>
        </div>

        <div class="form-group">
          <select name="content_type" id="content_type" required>
            <option value="" disabled selected>Content Type</option>
            <option value="image">Image</option>
            <option value="video">Video</option>
          </select>
          <div class="form-error" id="content_type_error">Please select content type</div>
        </div>

        <div class="form-group">
          <textarea name="content_desc" id="content_desc" placeholder="Content Description" rows="3" required></textarea>
          <div class="form-error" id="content_desc_error">Content description is required</div>
        </div>

        <div class="form-group">
          <input type="file" name="media_file" id="media_file" accept="image/*,video/*" required />
          <div class="form-error" id="media_file_error">Please select a file</div>
          <small style="color: #666; font-size: 0.8rem; margin-top: 5px; display: block;">
            Supported formats: JPG, PNG, GIF, MP4, MOV, AVI, WEBM (Max: 50MB)
          </small>
        </div>

        <div class="progress-container" id="progressContainer">
          <div class="progress-bar" id="progressBar"></div>
        </div>

        <button type="submit" id="submitBtn">
          <i class="fas fa-upload"></i> Upload Content
        </button>
      </form>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Dropdown functionality
    function toggleDropdown() {
      const dropdown = document.getElementById("mediaDropdown");
      const caretIcon = document.querySelector(".caret-icon");
      
      dropdown.classList.toggle("show");
      
      if (dropdown.classList.contains("show")) {
        caretIcon.style.transform = "rotate(180deg)";
      } else {
        caretIcon.style.transform = "rotate(0deg)";
      }
    }

    // Modal functionality
    function openUploadModal() {
      document.getElementById("uploadModal").style.display = "block";
      document.body.style.overflow = "hidden"; // Prevent background scrolling
    }

    function closeUploadModal() {
      document.getElementById("uploadModal").style.display = "none";
      document.body.style.overflow = "auto"; // Restore scrolling
      resetForm();
    }

    // Reset form function
    function resetForm() {
      document.getElementById("uploadForm").reset();
      clearErrors();
      hideProgress();
    }

    // Clear all error messages
    function clearErrors() {
      const errors = document.querySelectorAll('.form-error');
      const inputs = document.querySelectorAll('.input-error');
      
      errors.forEach(error => error.style.display = 'none');
      inputs.forEach(input => input.classList.remove('input-error'));
    }

    // Show error for specific field
    function showError(fieldId, message) {
      const field = document.getElementById(fieldId);
      const error = document.getElementById(fieldId + '_error');
      
      field.classList.add('input-error');
      error.textContent = message;
      error.style.display = 'block';
    }

    // Form validation
    function validateForm() {
      let isValid = true;
      clearErrors();

      // Validate content name
      const contentName = document.getElementById('content_name').value.trim();
      if (!contentName) {
        showError('content_name', 'Content name is required');
        isValid = false;
      } else if (contentName.length < 3) {
        showError('content_name', 'Content name must be at least 3 characters');
        isValid = false;
      }

      // Validate category
      const category = document.getElementById('category').value;
      if (!category) {
        showError('category', 'Please select a category');
        isValid = false;
      }

      // Validate content type
      const contentType = document.getElementById('content_type').value;
      if (!contentType) {
        showError('content_type', 'Please select content type');
        isValid = false;
      }

      // Validate description
      const contentDesc = document.getElementById('content_desc').value.trim();
      if (!contentDesc) {
        showError('content_desc', 'Content description is required');
        isValid = false;
      } else if (contentDesc.length < 10) {
        showError('content_desc', 'Description must be at least 10 characters');
        isValid = false;
      }

      // Validate file
      const mediaFile = document.getElementById('media_file');
      if (!mediaFile.files[0]) {
        showError('media_file', 'Please select a file');
        isValid = false;
      } else {
        const file = mediaFile.files[0];
        const maxSize = 50 * 1024 * 1024; // 50MB
        
        if (file.size > maxSize) {
          showError('media_file', 'File size must be less than 50MB');
          isValid = false;
        }

        // Validate file type
        const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'webm'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExtension)) {
          showError('media_file', 'Invalid file type. Allowed: ' + allowedTypes.join(', '));
          isValid = false;
        }
      }

      return isValid;
    }

    // Show progress bar
    function showProgress() {
      document.getElementById('progressContainer').style.display = 'block';
    }

    // Hide progress bar
    function hideProgress() {
      document.getElementById('progressContainer').style.display = 'none';
      document.getElementById('progressBar').style.width = '0%';
    }

    // Update progress bar
    function updateProgress(percent) {
      document.getElementById('progressBar').style.width = percent + '%';
    }

    // Form submission with AJAX
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      if (!validateForm()) {
        return;
      }

      const submitBtn = document.getElementById('submitBtn');
      const originalText = submitBtn.innerHTML;
      
      // Disable submit button and show loading
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<div class="spinner"></div>Uploading...';
      
      showProgress();
      
      // Simulate progress (you can implement real progress with XMLHttpRequest)
      let progress = 0;
      const progressInterval = setInterval(function() {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        updateProgress(progress);
      }, 200);

      // Create FormData and submit
      const formData = new FormData(this);
      
      fetch('upload-handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        clearInterval(progressInterval);
        updateProgress(100);
        
        setTimeout(() => {
          hideProgress();
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
          
          // Check if upload was successful (you might want to return JSON from PHP)
          if (data.includes('successful') || data.includes('success')) {
            closeUploadModal();
            showMessage('Content uploaded successfully!', 'success');
            
            // Refresh page after 2 seconds to show updated content
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            showMessage('Upload failed: ' + data, 'error');
          }
        }, 500);
      })
      .catch(error => {
        clearInterval(progressInterval);
        hideProgress();
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showMessage('Upload failed: ' + error.message, 'error');
      });
    });

    // Show success/error messages
    function showMessage(message, type) {
      const messageDiv = document.createElement('div');
      messageDiv.className = 'message ' + type + '-message';
      messageDiv.textContent = message;
      messageDiv.style.display = 'block';
      document.body.appendChild(messageDiv);
      
      // Hide after 4 seconds
      setTimeout(() => {
        messageDiv.style.display = 'none';
        document.body.removeChild(messageDiv);
      }, 4000);
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById("uploadModal");
      if (event.target === modal) {
        closeUploadModal();
      }
    };

    // Handle escape key to close modal
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeUploadModal();
      }
    });

    // Show PHP messages on page load
    window.addEventListener('load', function() {
      <?php if (!empty($upload_message)): ?>
        showMessage('<?php echo addslashes($upload_message); ?>', 'success');
      <?php endif; ?>
      
      <?php if (!empty($error_message)): ?>
        showMessage('<?php echo addslashes($error_message); ?>', 'error');
      <?php endif; ?>
    });

    // File input change handler
    document.getElementById('media_file').addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB
        const fileName = file.name;
        
        // Clear any previous errors
        this.classList.remove('input-error');
        document.getElementById('media_file_error').style.display = 'none';
        
        // Show file info
        const fileInfo = document.createElement('small');
        fileInfo.style.color = '#666';
        fileInfo.style.fontSize = '0.8rem';
        fileInfo.style.marginTop = '5px';
        fileInfo.style.display = 'block';
        fileInfo.textContent = `Selected: ${fileName} (${fileSize} MB)`;
        
        // Remove any existing file info
        const existingInfo = this.parentNode.querySelector('.file-info');
        if (existingInfo) {
          existingInfo.remove();
        }
        
        fileInfo.className = 'file-info';
        this.parentNode.appendChild(fileInfo);
      }
    });
  </script>
</body>
</html>
