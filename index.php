<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// your login/register code follows...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>RawVI</title>
    <link rel="stylesheet" href="css/index.css" />
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-left">
        <img src="images/LOGO.png" alt="RawVI Logo" class="logo" />
    </div>
    <div class="nav-right">
        <a href="about.php" class="nav-link">About</a>
        <a href="login.php" class="btn login">Log in</a>
        <a href="register.php" class="btn signup">Sign up</a>
    </div>
</div>

<!-- Header -->
<header class="header">
    <h1>Unleash <span class="highlight">Creative Energy</span></h1>
    <p>"A board filled with curated moments and visual dreams"</p>
</header>

<!-- Premium Content Grid -->
<div class="grid">
    <!-- Card 1 -->
    <div class="pin-card">
        <div class="img-wrapper">
            <img src="images/Forest.jpg" alt="Forest Glow" loading="lazy" />
            <div class="overlay">
                <div class="overlay-text">
                    <h3>Forest Glow</h3>
                    <p>Let the light breathe through leaves.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="pin-card">
        <div class="img-wrapper">
            <img src="images/Sanduk-ruit.jpg" alt="Nation's Personnel" loading="lazy" />
            <div class="overlay">
                <div class="overlay-text">
                    <h3>Nation's Personnel</h3>
                    <p>Representing country worldwide !</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="pin-card">
        <div class="img-wrapper">
            <img src="images/thakali set food.jpg" alt="Golden Bites" loading="lazy" />
            <div class="overlay">
                <div class="overlay-text">
                    <h3>Golden Bites</h3>
                    <p>Feed your senses. Frame the taste.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="pin-card">
        <div class="img-wrapper">
            <img src="images/Stupa buildin.webp" alt="Sky Kingdom" loading="lazy" />
            <div class="overlay">
                <div class="overlay-text">
                    <h3>Sky Kingdom</h3>
                    <p>Dreams reach higher than peaks.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 5 -->
    <div class="pin-card">
        <div class="img-wrapper">
            <img src="images/holi.webp" alt="Combinations of feeling" loading="lazy" />
            <div class="overlay">
                <div class="overlay-text">
                    <h3>Combinations of feeling</h3>
                    <p>Peace in every pinch !</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 6 -->
    <div class="pin-card">
        <div class="img-wrapper">
            <img src="images/SheyPhoksundo.webp" alt="Frame the Shot" loading="lazy" />
            <div class="overlay">
                <div class="overlay-text">
                    <h3>Frame the Shot</h3>
                    <p>Every moment deserves a masterpiece.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full-page Login Prompt Section -->
<section class="login-section">
    <h2>
        <a href="login.php" class="login-link">Login</a> to get more ideas on your projects !
    </h2>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <p>&copy; 2025 RawVI. All Rights Reserved.</p>
        <div class="social-links">
            <a href="https://facebook.com" target="_blank">Facebook</a>
            <a href="https://instagram.com" target="_blank">Instagram</a>
            <a href="https://twitter.com" target="_blank">Twitter</a>
        </div>
    </div>
</footer>

<script>
    window.addEventListener("scroll", function() {
        const loginText = document.querySelector(".login-section h2");
        const position = loginText.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;

        if(position < screenPosition) {
            loginText.classList.add("show");
        }
    });
</script>

</body>
</html>
