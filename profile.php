<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ========================
// Handle download record AJAX request
// ========================
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'download_record') {
        $content_id = $data['content_id'] ?? 0;
        if ($user_id && $content_id) {
            $stmt = $conn->prepare("INSERT INTO download (user_id, content_id, downloaded_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $user_id, $content_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit();
    }

    if ($action === 'delete_content') {
        $content_id = $data['content_id'] ?? 0;
        $tab = $data['tab'] ?? '';
        $success = false;

        if ($tab === 'bookmarks') {
            $stmt = $conn->prepare("DELETE FROM bookmark WHERE user_id = ? AND content_id = ?");
            $stmt->bind_param("ii", $user_id, $content_id);
            $success = $stmt->execute();
            $stmt->close();
        } elseif ($tab === 'downloads') {
            $stmt = $conn->prepare("DELETE FROM download WHERE user_id = ? AND content_id = ?");
            $stmt->bind_param("ii", $user_id, $content_id);
            $success = $stmt->execute();
            $stmt->close();
        } elseif ($tab === 'uploads') {
            // Delete uploaded content completely
            $stmt = $conn->prepare("DELETE FROM content WHERE id = ? AND uploaded_by = ?");
            $stmt->bind_param("ii", $content_id, $user_id);
            $success = $stmt->execute();
            $stmt->close();
        }

        echo json_encode(['success' => $success]);
        exit();
    }
}

// ========================
// Handle tab content AJAX request
// ========================
if (isset($_GET['tab'])) {
    $tab = $_GET['tab'];

    if ($tab === 'bookmarks') {
        $stmt = $conn->prepare("SELECT b.id AS bookmark_id, c.id, c.content_name, c.content_desc, c.file 
                                FROM bookmark AS b
                                JOIN content AS c ON b.content_id = c.id
                                WHERE b.user_id = ? ORDER BY b.saved_at DESC");
        $stmt->bind_param("i", $user_id);
    } elseif ($tab === 'downloads') {
        $stmt = $conn->prepare("SELECT c.id, c.content_name, c.content_desc, c.file 
                                FROM download AS d
                                JOIN content AS c ON d.content_id = c.id
                                WHERE d.user_id = ? ORDER BY d.downloaded_at DESC");
        $stmt->bind_param("i", $user_id);
    } elseif ($tab === 'uploads') {
        $stmt = $conn->prepare("SELECT id, content_name, content_desc, file 
                                FROM content WHERE uploaded_by = ? ORDER BY id DESC");
        $stmt->bind_param("i", $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p style='text-align:center; color:#7f8c99;'>No $tab available.</p>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo renderCard($row, $tab);
        }
    }
    exit();
}

// ========================
// Card render function
// ========================
function renderCard($row, $tab) {
    global $user_id;
    $file = basename($row['file']);
    $path = 'Admin/uploads/' . rawurlencode($file);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);
    $color = $tab === 'bookmarks' ? '#00d8ff' : ($tab === 'downloads' ? '#00e676' : '#ffca28');

    ob_start(); ?>
    <div class="pin" style="border-left: 4px solid <?= $color ?>; position: relative;">
        <!-- Delete button -->
        <button class="btn-delete" onclick="deleteContent(<?= $row['id'] ?>, '<?= $tab ?>')">✖</button>

        <?php if ($isImage): ?>
            <img src="<?= $path ?>" alt="<?= htmlspecialchars($row['content_name']) ?>">
        <?php elseif ($isVideo): ?>
            <video muted preload="metadata">
                <source src="<?= $path ?>" type="video/<?= $ext ?>">
            </video>
        <?php else: ?>
            <div class="no-preview">No preview<br><small>(<?= strtoupper($ext) ?>)</small></div>
        <?php endif; ?>

        <div class="pin-info">
            <p><?= htmlspecialchars($row['content_name']) ?></p>
            <small><?= htmlspecialchars($row['content_desc']) ?></small>

            <?php if ($tab === 'bookmarks'): ?>
                <button class="btn-download" onclick="recordDownload(<?= $user_id ?>, <?= $row['id'] ?>, '<?= $file ?>')">⬇️ Download</button>
            <?php endif; ?>

            <?php if ($tab === 'uploads'): ?>
                <a href="updateuploads.php?id=<?= $row['id'] ?>" class="btn-edit">✏️ Edit Details</a>
            <?php endif; ?>
        </div>
    </div>
    <?php return ob_get_clean();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Profile - RawVI</title>
<link rel="stylesheet" href="css/home.css" />
<link rel="stylesheet" href="profile.css" />
<style>
.tab-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 32px auto 24px;
}
.tab-toggle.active {
    background: #00d8ff;
    color: #fff;
}
.tab-content-area {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 240px));
    justify-content: center;
    gap: 24px;
    max-width: 1280px;
    margin: auto;
    padding: 16px;
}
.pin {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    overflow: hidden;
}
.pin img, .pin video, .no-preview {
    width: 100%;
    height: 160px;
    object-fit: cover;
}
.no-preview {
    background: #2a2f37;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 0.9rem;
    text-align: center;
}
.pin-info {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.btn-download {
    margin-top: 8px;
    background: linear-gradient(135deg, #00b894, #008c74);
    color: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}
.btn-download:hover {
    background: linear-gradient(135deg, #008c74, #00695c);
}
.btn-delete {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(255,0,0,0.8);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.btn-delete:hover {
    background: rgba(255,0,0,1);
}
.btn-edit {
    margin-top: 8px;
    background: linear-gradient(135deg, #3498db, #2c80b4);
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}
.btn-edit:hover {
    background: linear-gradient(135deg, #2c80b4, #1f5a85);
    color: #fff;
}

</style>
</head>
<body>

<header class="navbar">
<h1><a href="home.php" class="logo-link">RawVI</a></h1>
<nav>
    <a href="profile.php">Welcome, <?= htmlspecialchars($username) ?></a>
    <a href="upload_content.php">Upload</a>
    <a href="index.php" style="color: #ff5252;">Logout</a>
</nav>
</header>

<div class="tab-buttons">
  <button class="tab-toggle active" data-tab="bookmarks">🔖 Bookmarks</button>
  <button class="tab-toggle" data-tab="downloads">⬇️ Downloads</button>
  <button class="tab-toggle" data-tab="uploads">📤 Uploads</button>
</div>

<div id="tab-content" class="tab-content-area">
  <p style="text-align:center; color:#999;">Loading bookmarks...</p>
</div>

<script>
const buttons = document.querySelectorAll('.tab-toggle');
const container = document.getElementById('tab-content');

function loadTab(tab) {
  container.innerHTML = "<p style='text-align:center; color:#999;'>Loading " + tab + "...</p>";
  fetch(`profile.php?tab=${tab}`)
    .then(res => res.text())
    .then(data => container.innerHTML = data);
}

buttons.forEach(btn => {
  btn.addEventListener('click', () => {
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadTab(btn.dataset.tab);
  });
});

// Load bookmarks first
loadTab("bookmarks");

// ========================
// Record download & trigger actual download
// ========================
function recordDownload(userId, contentId, fileName) {
  fetch(`profile.php?action=download_record`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, content_id: contentId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const link = document.createElement('a');
      link.href = 'Admin/uploads/' + encodeURIComponent(fileName);
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } else {
      alert('Failed to record download!');
    }
  });
}

// ========================
// Delete content function
// ========================
function deleteContent(contentId, tab) {
  if (!confirm(`Are you sure you want to delete this content from ${tab}?`)) return;

  fetch(`profile.php?action=delete_content`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ content_id: contentId, tab: tab })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      loadTab(tab); // reload current tab
    } else {
      alert('Failed to delete content!');
    }
  });
}
</script>

</body>
</html>
