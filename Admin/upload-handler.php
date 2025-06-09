<?php
// Start session and check if admin logged in
session_start();
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define absolute path to uploads directory
    $upload_dir = __DIR__ . "/uploads/";

    // Create upload directory if not exists, with 0755 permissions
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $_SESSION['upload_error'] = "Failed to create uploads directory. Check server permissions.";
            header("Location: dashboard.php");
            exit();
        }
    }

    // Check if uploads directory is writable
    if (!is_writable($upload_dir)) {
        $_SESSION['upload_error'] = "Uploads directory is not writable. Check permissions.";
        header("Location: dashboard.php");
        exit();
    }

    // Sanitize and trim inputs to avoid empty values or injection
    $content_name = trim($_POST['content_name'] ?? '');
    $category = $_POST['category'] ?? '';
    $content_type = $_POST['content_type'] ?? '';
    $content_desc = trim($_POST['content_desc'] ?? '');
    $file = $_FILES['media_file'] ?? null;

    // Validate required inputs
    if (empty($content_name) || empty($category) || empty($content_type) || empty($content_desc) || !$file) {
        $_SESSION['upload_error'] = "All fields and file upload are required!";
        header("Location: dashboard.php");
        exit();
    }

    // Check for file upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => "File too large (server limit)",
            UPLOAD_ERR_FORM_SIZE => "File too large (form limit)",
            UPLOAD_ERR_PARTIAL => "Partial upload",
            UPLOAD_ERR_NO_FILE => "No file uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Can't write file to disk",
            UPLOAD_ERR_EXTENSION => "Upload stopped by extension"
        ];

        $error_msg = $error_messages[$file['error']] ?? "Unknown upload error";
        $_SESSION['upload_error'] = "File upload error: " . $error_msg;
        header("Location: dashboard.php");
        exit();
    }

    // Limit file size to 50MB
    if ($file['size'] > 50 * 1024 * 1024) {
        $_SESSION['upload_error'] = "File size too large! Max 50MB allowed.";
        header("Location: dashboard.php");
        exit();
    }

    // Extract file extension safely
    $original_filename = basename($file['name']);
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

    // Allowed extensions
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'webm'];
    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['upload_error'] = "Unsupported file type! Allowed: " . implode(', ', $allowed_types);
        header("Location: dashboard.php");
        exit();
    }

    // Validate MIME type for extra security
    $allowed_mimes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
        'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'
    ];

    $file_tmp = $file['tmp_name'];
    $file_mime = mime_content_type($file_tmp);
    if (!in_array($file_mime, $allowed_mimes)) {
        $_SESSION['upload_error'] = "Invalid file format! Detected type: " . $file_mime;
        header("Location: dashboard.php");
        exit();
    }

    // Generate unique filename (prevents overwriting)
    $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $unique_filename;

    // Move uploaded file to uploads directory
    if (!move_uploaded_file($file_tmp, $target_file)) {
        $_SESSION['upload_error'] = "Failed to move uploaded file.";
        header("Location: dashboard.php");
        exit();
    }

    // Set file permission to readable
    chmod($target_file, 0644);

    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "rawvi");

    if (!$conn) {
        // If DB connection fails, delete uploaded file to avoid orphan file
        unlink($target_file);
        $_SESSION['upload_error'] = "Database connection failed: " . mysqli_connect_error();
        header("Location: dashboard.php");
        exit();
    }

    // Use relative path for DB, not full system path
    $db_file_path = "uploads/" . $unique_filename;

    // Prepare SQL statement with placeholders
    $sql = "INSERT INTO content (content_name, category, content_type, content_desc, file_path, uploaded_by, upload_date) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        // Delete uploaded file if SQL prepare fails
        unlink($target_file);
        $_SESSION['upload_error'] = "SQL prepare failed: " . mysqli_error($conn);
        mysqli_close($conn);
        header("Location: dashboard.php");
        exit();
    }

    // Bind parameters to the SQL statement
    mysqli_stmt_bind_param($stmt, "ssssss",
        $content_name,
        $category,
        $content_type,
        $content_desc,
        $db_file_path,
        $_SESSION['email']
    );

    // Execute statement and check result
    if (!mysqli_stmt_execute($stmt)) {
        unlink($target_file);
        $_SESSION['upload_error'] = "Database insert failed: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: dashboard.php");
        exit();
    }

    // Close statement and DB connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Success! Set success message and redirect
    $_SESSION['upload_success'] = "Content uploaded successfully!";
    header("Location: view.php");
    exit();

} else {
    // Not POST method
    $_SESSION['upload_error'] = "Invalid request method.";
    header("Location: dashboard.php");
    exit();
}
?>
