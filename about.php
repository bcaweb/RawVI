<?php
// about.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>About - RawVI</title>
    <link rel="stylesheet" href="css/about.css" />
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-left">
        <img src="images/LOGO.png" alt="RawVI Logo" class="logo" />
    </div>
    <div class="nav-right">
        <a href="index.php" class="nav-link">Home</a>
        <a href="about.php" class="nav-link active">About</a>
        <a href="login.php" class="btn login">Log in</a>
        <a href="register.php" class="btn signup">Sign up</a>
    </div>
</div>

<!-- Hero / Intro -->
<section class="hero">
    <h1>About <span class="highlight">RawVI</span></h1>
    <p>A creative space for visual thinkers and dreamers ✨</p>
</section>

<!-- Mission -->
<section class="mission">
    <h2>Our Mission</h2>
    <p>We aim to empower creators by providing a board filled with curated moments, 
       creative inspirations, and raw content ideas that spark imagination.</p>
</section>

<!-- Features -->
<section class="features">
    <h2>What We Offer</h2>
    <div class="feature-grid">
        <div class="feature-card">🎨 Creative Boards<br><span>Organize and collect ideas</span></div>
        <div class="feature-card">🌍 Inspiration Gallery<br><span>Browse and share moments</span></div>
        <div class="feature-card">🤝 Community<br><span>Connect & collaborate</span></div>
        <div class="feature-card">🌟 Premium Content<br><span>Exclusive inspirations</span></div>
    </div>
</section>

<!-- Story -->
<section class="story">
    <h2>Our Story</h2>
    <p>RawVI was started with a simple belief: creativity should be free, raw, and limitless. 
       From a small idea, it grew into a platform where creators can explore and share without boundaries.</p>
</section>

<!-- Team -->
<section class="team">
    <h2>Meet the Team</h2>
    <div class="team-grid">
        <div class="team-card">
            <img src="images/AMIT.jpg" alt="Sathi">
            <h3>Amit Bam</h3>
            <p>Founder & Creative Head</p>
        </div>
        <div class="team-card">
            <img src="images/HARDIK.jpg" alt="Teammate">
            <h3>Hardik Lekhak</h3>
            <p>Design & Development</p>
        </div>
         <div class="team-card">
            <img src="images/GOKARNA.JPG" alt="Teammate">
            <h3>Gokarna Rawal</h3>
            <p>Marketing</p>
        </div>
        <div class="team-card">
            <img src="images/GAURAV.jpg" alt="Teammate">
            <h3>Gaurav Bohara</h3>
            <p>Research</p>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta">
    <h2>Join RawVI Today</h2>
    <p>Start creating your own visual journey and share your ideas with the world.</p>
    <a href="register.php" class="btn cta-btn">Get Started</a>
</section>

<!-- Footer -->
<footer class="footer">
    <p>&copy; 2025 RawVI. All Rights Reserved.</p>
    <div class="socials">
        <a href="#">Facebook</a> |
        <a href="#">Instagram</a> |
        <a href="#">Twitter</a>
    </div>
</footer>

</body>
</html>
