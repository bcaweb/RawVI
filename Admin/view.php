<?php
// Start session and verify login
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// DB connection
$conn = mysqli_connect("localhost", "root", "", "rawvi");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all content with proper column names
$sql = "SELECT * FROM content ORDER BY upload_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RawVI Content Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(121, 82, 179, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        .back-btn:hover {
            background: rgba(121, 82, 179, 1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(121, 82, 179, 0.4);
        }

        .stats-bar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            color: white;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
        }

        .filter-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-item select, .filter-item input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .filter-item select:focus, .filter-item input:focus {
            border-color: rgb(121, 82, 179);
            box-shadow: 0 0 0 3px rgba(121, 82, 179, 0.1);
        }

        .search-box {
            flex: 1;
            min-width: 250px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(121, 82, 179, 0.2);
        }

        .card-media {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .card img, .card video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover img, .card:hover video {
            transform: scale(1.05);
        }

        .media-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(121, 82, 179, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .card-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .meta-tag {
            background: linear-gradient(45deg, rgb(121, 82, 179), rgb(101, 62, 159));
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-footer {
            border-top: 1px solid #f0f0f0;
            padding: 15px 20px;
            background: rgba(248, 249, 250, 0.8);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #666;
        }

        .uploader-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .upload-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .no-content {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            margin-top: 50px;
        }

        .no-content i {
            font-size: 4rem;
            color: rgb(121, 82, 179);
            margin-bottom: 20px;
        }

        .no-content h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .no-content p {
            color: #666;
            font-size: 1rem;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: white;
        }

        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .view-btn {
            background: linear-gradient(45deg, rgb(121, 82, 179), rgb(101, 62, 159));
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(121, 82, 179, 0.3);
        }

        /* Modal for full-size view */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            overflow: hidden;
        }

        .modal-content img, .modal-content video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: white;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
        }

        .modal-close:hover {
            color: rgb(121, 82, 179);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .back-btn {
                position: relative;
                margin-bottom: 20px;
                align-self: flex-start;
            }

            .content-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stats-bar {
                flex-direction: column;
                gap: 15px;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .card-content {
                padding: 15px;
            }

            .card-footer {
                padding: 10px 15px;
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="header">
        <h1><i class="fas fa-photo-video"></i> Content Library</h1>
        <p>Browse and manage your uploaded media content</p>
    </div>

    <?php
    // Get statistics
    $stats_sql = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN content_type = 'image' THEN 1 END) as images,
        COUNT(CASE WHEN content_type = 'video' THEN 1 END) as videos,
        COUNT(DISTINCT category) as categories
        FROM content";
    $stats_result = mysqli_query($conn, $stats_sql);
    $stats = mysqli_fetch_assoc($stats_result);
    ?>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Files</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['images']; ?></div>
            <div class="stat-label">Images</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['videos']; ?></div>
            <div class="stat-label">Videos</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['categories']; ?></div>
            <div class="stat-label">Categories</div>
        </div>
    </div>

    <div class="filter-bar">
        <div class="filter-item search-box">
            <i class="fas fa-search" style="color: #666;"></i>
            <input type="text" id="searchInput" placeholder="Search content..." onkeyup="filterContent()">
        </div>
        <div class="filter-item">
            <i class="fas fa-filter" style="color: #666;"></i>
            <select id="categoryFilter" onchange="filterContent()">
                <option value="">All Categories</option>
                <?php
                $cat_sql = "SELECT DISTINCT category FROM content ORDER BY category";
                $cat_result = mysqli_query($conn, $cat_sql);
                while ($cat_row = mysqli_fetch_assoc($cat_result)) {
                    echo '<option value="' . htmlspecialchars($cat_row['category']) . '">' . htmlspecialchars($cat_row['category']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="filter-item">
            <i class="fas fa-layer-group" style="color: #666;"></i>
            <select id="typeFilter" onchange="filterContent()">
                <option value="">All Types</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
            </select>
        </div>
    </div>

    <div class="content-grid" id="contentGrid">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $file_path = htmlspecialchars($row['file_path']);
                $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $upload_date = date('M j, Y', strtotime($row['upload_date']));

                echo '<div class="card" data-category="' . htmlspecialchars($row['category']) . '" data-type="' . htmlspecialchars($row['content_type']) . '" data-name="' . htmlspecialchars($row['content_name']) . '">';
                
                echo '<div class="card-media">';
                
                // Display image or video
                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($row['content_name']) . '" onclick="openModal(this)">';
                    echo '<div class="media-overlay"><i class="fas fa-image"></i> Image</div>';
                } elseif (in_array($file_ext, ['mp4', 'mov', 'avi', 'webm'])) {
                    echo '<video onclick="openModal(this)" style="cursor: pointer;">';
                    echo '<source src="' . $file_path . '" type="video/' . $file_ext . '">';
                    echo 'Your browser does not support the video tag.';
                    echo '</video>';
                    echo '<div class="media-overlay"><i class="fas fa-video"></i> Video</div>';
                } else {
                    echo '<div style="height: 100%; display: flex; align-items: center; justify-content: center; background: #f5f5f5; color: #999;">';
                    echo '<i class="fas fa-file-alt" style="font-size: 3rem;"></i>';
                    echo '</div>';
                    echo '<div class="media-overlay"><i class="fas fa-file"></i> File</div>';
                }
                
                echo '</div>';

                echo '<div class="card-content">';
                echo '<h3 class="card-title">' . htmlspecialchars($row['content_name']) . '</h3>';
                echo '<p class="card-description">' . htmlspecialchars($row['content_desc']) . '</p>';
                
                echo '<div class="card-meta">';
                echo '<span class="meta-tag"><i class="fas fa-tag"></i> ' . htmlspecialchars($row['category']) . '</span>';
                echo '<span class="meta-tag"><i class="fas fa-layer-group"></i> ' . htmlspecialchars($row['content_type']) . '</span>';
                echo '</div>';
                
                echo '</div>';

                echo '<div class="card-footer">';
                echo '<div class="uploader-info">';
                echo '<i class="fas fa-user"></i>';
                echo '<span>' . htmlspecialchars($row['uploaded_by']) . '</span>';
                echo '</div>';
                echo '<div class="upload-date">';
                echo '<i class="fas fa-calendar"></i>';
                echo '<span>' . $upload_date . '</span>';
                echo '</div>';
                echo '</div>';

                echo '</div>';
            }
        } else {
            echo '<div class="no-content">';
            echo '<i class="fas fa-inbox"></i>';
            echo '<h3>No Content Found</h3>';
            echo '<p>Start uploading some amazing content to see it here!</p>';
            echo '</div>';
        }

        mysqli_close($conn);
        ?>
    </div>

    <!-- Modal for full-size view -->
    <div id="mediaModal" class="modal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-content" id="modalContent">
        </div>
    </div>

    <script>
        // Filter functionality
        function filterContent() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const category = card.getAttribute('data-category');
                const type = card.getAttribute('data-type');

                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = !categoryFilter || category === categoryFilter;
                const matchesType = !typeFilter || type === typeFilter;

                if (matchesSearch && matchesCategory && matchesType) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Modal functionality
        function openModal(element) {
            const modal = document.getElementById('mediaModal');
            const modalContent = document.getElementById('modalContent');
            
            if (element.tagName === 'IMG') {
                modalContent.innerHTML = '<img src="' + element.src + '" alt="' + element.alt + '">';
            } else if (element.tagName === 'VIDEO') {
                const source = element.querySelector('source');
                modalContent.innerHTML = '<video controls autoplay><source src="' + source.src + '" type="' + source.type + '"></video>';
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('mediaModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Stop any playing video
            const video = modal.querySelector('video');
            if (video) {
                video.pause();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('mediaModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                filterContent();
            }
        });

        // Lazy loading for images
        const images = document.querySelectorAll('img');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '0';
                    img.onload = () => {
                        img.style.transition = 'opacity 0.3s ease';
                        img.style.opacity = '1';
                    };
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    </script>
</body>
</html>
