<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$searchTerm = '';
$results = [];

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $searchTerm = mysqli_real_escape_string($con, trim($_GET['q']));

    $sql = "SELECT * FROM content
            WHERE content_desc LIKE '%$searchTerm%'";
            //  OR tags LIKE '%$searchTerm%'";
    
    $query = mysqli_query($con, $sql);
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $results[] = $row;
        }
    } else {
        die("Query failed: " . mysqli_error($con));
    }
}
?>

<?php include('header.php'); ?>
<link rel="stylesheet" href="search.css">

<div class="layout-container">
    <?php include('sidebar.php'); ?>

    <div class="main-container">
        <div class="header">
            <h1>🔍 Search Content</h1>
        </div>

        <div class="main-dashboard">
            <form method="GET" action="search.php" class="search-form">
                <input 
                    type="search" 
                    name="q" 
                    placeholder="Enter keywords..." 
                    value="<?php echo htmlspecialchars($searchTerm); ?>" 
                    required 
                />
                <button type="submit">Search</button>
            </form>

            <div class="search-results">
                <?php if ($searchTerm !== ''): ?>
                    <h2 class="results-title">Results for: "<?php echo htmlspecialchars($searchTerm); ?>"</h2>

                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $content): ?>
                            <div class="result" style="margin-bottom:30px;">
                                <h3><?php echo htmlspecialchars($content['content_name']); ?></h3>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($content['category']); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($content['content_type']); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($content['content_desc']); ?></p>
                                <p><strong>Tags:</strong> <?php echo htmlspecialchars($content['tags']); ?></p>
                                <p><small>Uploaded by: <?php echo htmlspecialchars($content['uploaded_by']); ?> on <?php echo htmlspecialchars($content['upload_date']); ?></small></p>

                                <?php 
                                    $file = htmlspecialchars($content['file_path']);
                                    $filePath = "uploads/" . $file;
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                ?>

                                <?php if (file_exists($filePath)): ?>
                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                        <img src="<?php echo $filePath; ?>" alt="Image for <?php echo htmlspecialchars($content['content_name']); ?>" style="max-width: 300px; height: auto; margin-top:10px;" />
                                    <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])): ?>
                                        <video controls width="320" style="margin-top:10px;">
                                            <source src="<?php echo $filePath; ?>" type="video/<?php echo $ext; ?>">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php elseif ($ext === 'pdf'): ?>
                                        <embed src="<?php echo $filePath; ?>" type="application/pdf" width="300" height="400" style="margin-top:10px;" />
                                    <?php else: ?>
                                        <!-- Button triggers popup -->
                                        <!-- <button class="view-file-btn" data-file="<?php echo $filePath; ?>" data-ext="<?php echo $ext; ?>" style="margin-top:10px; padding:8px 12px; cursor:pointer;">
                                            View File
                                        </button> -->
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p><em>File not found on server.</em></p>
                                <?php endif; ?>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-results">No content found matching your search.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Popup Overlay -->
        <div id="filePopup" class="file-popup-overlay" style="display:none;">
            <div class="file-popup-content">
                <span id="closePopup" class="close-popup">&times;</span>
                <div id="popupBody" style="text-align:center;">
                    <!-- File preview loads here -->
                </div>
            </div>
        </div>

        <?php include('footer.php'); ?>
    </div>
</div>

<!-- Popup styles -->
<style>
/* Keep your existing styles from search.css here, then add: */

.view-file-btn {
  background-color: #78c4d4;
  color: #1c1c2b;
  border: none;
  padding: 10px 16px;
  border-radius: 20px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s ease;
  margin-top: 10px;
  display: inline-block;
  user-select: none;
}

.view-file-btn:hover {
  background-color: #5aa9b8;
}

.file-popup-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.file-popup-content {
  background: #1e2836;
  padding: 25px;
  max-width: 90%;
  max-height: 90%;
  overflow-y: auto;
  border-radius: 12px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0,0,0,0.6);
  color: #e0e0e0;
}

.close-popup {
  position: absolute;
  top: 12px;
  right: 18px;
  font-size: 32px;
  font-weight: bold;
  color: #78c4d4;
  cursor: pointer;
  user-select: none;
  transition: color 0.3s ease;
}

.close-popup:hover {
  color: #a3d2ca;
}

.file-popup-content video,
.file-popup-content embed,
.file-popup-content img {
  max-width: 100%;
  max-height: 80vh;
  border-radius: 8px;
  box-shadow: 0 0 15px rgba(120, 196, 212, 0.7);
}
</style>

<!-- Popup script -->
<script>

document.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('filePopup');
    const popupBody = document.getElementById('popupBody');
    const closeBtn = document.getElementById('closePopup');

    document.querySelectorAll('.view-file-btn').forEach(button => {
        button.addEventListener('click', () => {
            const file = button.getAttribute('data-file');
            const ext = button.getAttribute('data-ext').toLowerCase();

            let content = '';

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                content = `<img src="${file}" style="max-width: 100%; height: auto;" alt="Image Preview">`;
            } else if (['mp4', 'webm', 'ogg', 'mov'].includes(ext)) {
                content = `<video controls autoplay style="max-width: 100%; max-height: 80vh;">
                              <source src="${file}" type="video/${ext}">
                              Your browser does not support the video tag.
                           </video>`;
            } else if (ext === 'pdf') {
                content = `<embed src="${file}" type="application/pdf" width="100%" height="600px" />`;
            } else {
                content = `<p>Cannot preview this file type. <a href="${file}" download>Download File</a></p>`;
            }

            popupBody.innerHTML = content;
            popup.style.display = 'flex';
        });
    });

    closeBtn.addEventListener('click', () => {
        popup.style.display = 'none';
        popupBody.innerHTML = '';
    });

    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            popup.style.display = 'none';
            popupBody.innerHTML = '';
        }
    });
});

</script>
