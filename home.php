<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];

require 'db.php';

$searchTerm = '';
$condition = '';
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $searchTerm = mysqli_real_escape_string($conn, trim($_GET['q']));
    $condition = "WHERE content_desc LIKE '%$searchTerm%'";
}

$query = "SELECT id, content_name, content_desc, file FROM content $condition ORDER BY upload_date DESC LIMIT 20";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RawVI — Showcase Your Talent</title>
  <link rel="stylesheet" href="css/home.css" />
 <style>
/* Fixed bottom Read More section with glass effect */
.readmore-section {
    position: fixed;
    bottom: 20px;              /* slightly above the bottom edge */
    left: 50%;
    transform: translateX(-50%) translateY(100%);
    width: 90%;
    max-width: 600px;          /* smaller and centered */
    background: rgba(255, 255, 255, 0.12); /* glass-like effect */
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 14px;
    color: #fff;
    padding: 18px 22px;
    z-index: 999;
    max-height: 25%;           /* smaller height */
    overflow-y: auto;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    transition: transform 0.3s ease-in-out, opacity 0.3s ease;
    opacity: 0;
}
.readmore-section.active {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}
.readmore-close {
    cursor: pointer;
    float: right;
    font-size: 16px;
    font-weight: bold;
    color: #00c8ff;
}
.readmore-section h3 {
    margin-bottom: 10px;
    font-size: 18px;
    color: #0f555eff;
}
.readmore-section p {
    font-size: 14px;
    line-height: 1.5;
    color: #1c2324ff;
}
</style>

</head>
<body>

<header class="navbar">
  <h1><a href="home.php" class="logo-link">RawVI</a></h1>
  <nav>
    <a href="profile.php">Welcome, <?php echo htmlspecialchars($username); ?></a>
    <a href="upload_content.php">Upload</a>
    <a href="index.php" style="color: #ff5252;">Logout</a>
  </nav>
</header>

<form method="GET" class="filter-bar">
  <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search in description..." />
  <button type="submit">Search</button>
</form>

<!-- Fixed Read More Section -->
<section class="readmore-section" id="readmoreSection">
  <span class="readmore-close" onclick="closeReadMore()">✖</span>
  <h3>Click Read More to view details</h3>
  <p>The full description of selected content will appear here.</p>
</section>

<!-- Content Grid -->
<div id="pinGrid">
  <?php
  if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          $fileRaw = $row['file'];
          $file = basename($fileRaw);
          $filePath = 'Admin/uploads/' . rawurlencode($file);
          $realPath = __DIR__ . '/Admin/uploads/' . $file;
          $title = htmlspecialchars($row['content_name']);
          $desc = htmlspecialchars($row['content_desc']);
          $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
          $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);

          echo '<div class="pin">';
          if (file_exists($realPath)) {
              echo '<div onclick="openModal(\'' . $filePath . '\', \'' . $ext . '\')" style="cursor:pointer">';
              if ($isImage) {
                  echo '<img src="' . $filePath . '" alt="' . $title . '" />';
              } elseif ($isVideo) {
                  echo '<video muted preload="metadata">
                          <source src="' . $filePath . '" type="video/' . $ext . '">
                        </video>';
              } else {
                  echo '<div style="height:200px;display:flex;align-items:center;justify-content:center;background:#333;color:#999;">
                          No preview<br><small>(' . strtoupper($ext) . ')</small>
                        </div>';
              }
              echo '</div>';
          } else {
              echo '<div style="height:200px;display:flex;align-items:center;justify-content:center;background:#333;color:#f88;">
                      File missing or corrupted
                    </div>';
          }

          // Fixed height description with scroll
          echo '<div class="pin-description">';
          echo '<p>' . $desc . '</p>';
          echo '</div>';

          echo '<div class="pin-actions">';
          echo '<button class="btn-download" onclick="logDownload(' . $row['id'] . ', \'' . $filePath . '\')">⬇️ Download</button>';
          echo '<button class="btn-bookmark" onclick="saveBookmark(' . $row['id'] . ')">🔖 Save</button>';
          echo '<button class="btn-readmore" onclick="showReadMore(\'' . addslashes($title) . '\', \'' . addslashes($desc) . '\')">Read More</button>';
          echo '</div></div>';
      }
  } else {
      echo "<p style='padding: 40px; font-size: 1.1rem;'>No content found matching your search.</p>";
  }
  ?>
</div>

<!-- Modal for preview -->
<div id="contentModal" class="modal">
  <span class="modal-close" onclick="closeModal()">&times;</span>
  <div class="modal-content" id="modalBody"></div>
</div>

<script>
function openModal(filePath, ext) {
  const modal = document.getElementById("contentModal");
  const modalBody = document.getElementById("modalBody");
  modal.style.display = "flex";
  if (["jpg","jpeg","png","gif","webp"].includes(ext)) {
    modalBody.innerHTML = `<img src="${filePath}" alt="content">`;
  } else if (["mp4","mov","webm"].includes(ext)) {
    modalBody.innerHTML = `<video controls autoplay><source src="${filePath}" type="video/${ext}">Your browser does not support video.</video>`;
  } else {
    modalBody.innerHTML = `<p style='color:#fff'>No preview available</p>`;
  }
}

function closeModal() {
  const modal = document.getElementById("contentModal");
  modal.style.display = "none";
  document.getElementById("modalBody").innerHTML = "";
}

function saveBookmark(contentId) {
  fetch('bookmark.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'content_id=' + encodeURIComponent(contentId)
  })
  .then(response => response.text())
  .then(message => alert(message))
  .catch(err => alert("Failed to bookmark"));
}

function logDownload(contentId, filePath) {
  fetch('download.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'content_id=' + encodeURIComponent(contentId)
  });

  const link = document.createElement('a');
  link.href = filePath;
  link.download = '';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function showReadMore(title, desc) {
    const section = document.getElementById('readmoreSection');
    section.innerHTML = `<span class="readmore-close" onclick="closeReadMore()">✖</span>
                         <h3>${title}</h3>
                         <p>${desc}</p>`;
    section.classList.add('active');
    section.scrollIntoView({ behavior: 'smooth' });
}

function closeReadMore() {
  const section = document.getElementById('readmoreSection');
  section.classList.remove('active');
}
</script>

</body>
</html>
