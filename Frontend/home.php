<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RawVI — Showcase Your Talent</title>
  <style>
    /* Reset */
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
      color: #e0e0e0;
    }

    body {
      background: #121212;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px 15px 40px;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    header {
      width: 100%;
      max-width: 1280px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #2c2c2c;
    }

    header h1 {
      font-weight: 900;
      font-size: 2.5rem;
      letter-spacing: 3px;
      color: #00bcd4; /* cool cyan accent */
      user-select: none;
    }

    nav a {
      text-decoration: none;
      color: #b0bec5;
      font-weight: 600;
      margin-left: 28px;
      padding: 8px 18px;
      border-radius: 6px;
      transition: all 0.25s ease;
      user-select: none;
      font-size: 0.95rem;
    }

    nav a:hover {
      color: #00bcd4;
      background-color: rgba(0, 188, 212, 0.15);
      box-shadow: 0 4px 12px rgba(0, 188, 212, 0.25);
    }

    /* Filter Bar */
    .filter-bar {
      max-width: 1280px;
      width: 100%;
      margin: 30px auto 40px;
      display: flex;
      justify-content: flex-start;
      gap: 16px;
    }

    .filter-bar input[type="text"] {
      flex-grow: 1;
      max-width: 480px;
      background-color: #1f1f1f;
      border: none;
      border-radius: 12px;
      padding: 14px 20px;
      font-size: 1rem;
      color: #cfd8dc;
      box-shadow: inset 0 0 8px rgba(0, 188, 212, 0.3);
      outline-offset: 3px;
      transition: box-shadow 0.3s ease;
      user-select: text;
    }
    .filter-bar input[type="text"]::placeholder {
      color: #546e7a;
      font-style: italic;
    }
    .filter-bar input[type="text"]:focus {
      box-shadow: inset 0 0 14px rgba(0, 188, 212, 0.8);
      background-color: #121212;
      color: #fff;
    }

    .filter-bar button {
      background: #00bcd4;
      color: #121212;
      font-weight: 700;
      padding: 14px 30px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 1rem;
      box-shadow: 0 6px 16px rgba(0, 188, 212, 0.6);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
    }
    .filter-bar button:hover {
      background: #00a1b8;
      box-shadow: 0 8px 22px rgba(0, 161, 184, 0.8);
    }

    /* Pin Grid */
    #pinGrid {
      max-width: 1280px;
      width: 100%;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 26px;
    }

    .pin {
      background: #1e1e1e;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.7);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      user-select: none;
    }

    .pin:hover {
      transform: translateY(-10px);
      box-shadow: 0 12px 36px rgba(0, 188, 212, 0.7);
    }

    .pin img {
      width: 100%;
      height: 190px;
      object-fit: cover;
      flex-shrink: 0;
      transition: transform 0.3s ease;
      border-top-left-radius: 20px;
      border-top-right-radius: 20px;
      background-color: #000;
    }

    .pin:hover img {
      transform: scale(1.05);
    }

    .pin p {
      padding: 16px 18px;
      font-weight: 600;
      font-size: 1.15rem;
      color: #e0e0e0;
      flex-grow: 1;
      letter-spacing: 0.03em;
      text-shadow: 0 0 8px rgba(0, 188, 212, 0.4);
      user-select: text;
    }

    .pin-actions {
      padding: 0 18px 22px;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      gap: 18px;
    }

    .btn {
      border: none;
      border-radius: 999px;
      padding: 10px 24px;
      font-weight: 700;
      font-size: 0.95rem;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(0, 188, 212, 0.5);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
      display: flex;
      align-items: center;
      gap: 8px;
      color: #121212;
    }

    .btn-like {
      background-color: #00bcd4;
      color: #121212;
    }
    .btn-like.liked {
      background-color: #007f8a;
      box-shadow: 0 5px 20px rgba(0, 127, 138, 0.7);
      color: #a1e7f0;
    }
    .btn-like:hover {
      background-color: #0099b3;
      box-shadow: 0 7px 26px rgba(0, 153, 179, 0.8);
      color: #e0f7fa;
    }

    .btn-download {
      background-color: #00e676;
      color: #121212;
      box-shadow: 0 4px 14px rgba(0, 230, 118, 0.6);
    }
    .btn-download:hover {
      background-color: #00c853;
      box-shadow: 0 6px 20px rgba(0, 200, 83, 0.8);
      color: #e0f7fa;
    }

    /* Responsive */
    @media (max-width: 720px) {
      header h1 {
        font-size: 2rem;
      }

      nav a {
        margin-left: 14px;
        padding: 6px 12px;
        font-size: 0.85rem;
      }

      .filter-bar {
        flex-direction: column;
        gap: 12px;
      }

      .filter-bar input[type="text"] {
        max-width: 100%;
        font-size: 0.95rem;
        padding: 12px 16px;
      }

      .filter-bar button {
        width: 100%;
        padding: 12px 0;
        font-size: 1rem;
      }

      #pinGrid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
      }

      .pin img {
        height: 160px;
      }
    }

    @media (max-width: 400px) {
      #pinGrid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      }

      .pin img {
        height: 130px;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>RawVI</h1>
    <nav>
      <a href="profile.php">Welcome, <?php echo htmlspecialchars($username); ?></a>
      <a href="upload_pins.php">Upload</a>
      <a href="logout.php" style="color: #ff5252;">Logout</a>
    </nav>
  </header>

  <div class="filter-bar">
    <input type="text" id="tagFilter" placeholder="Search by tags, separated by commas..." />
    <button id="filterBtn">Search</button>
  </div>

  <div id="pinGrid"></div>

  <script>
    let page = 1;
    let loading = false;

    function loadPins(filterTags = "") {
      if (loading) return;
      loading = true;

      fetch(`fetch_pins.php?page=${page}&tags=${encodeURIComponent(filterTags)}`)
        .then(res => res.json())
        .then(data => {
          const grid = document.getElementById("pinGrid");
          if (page === 1) grid.innerHTML = ""; // clear previous pins if fresh filter

          data.forEach(pin => {
            const div = document.createElement("div");
            div.className = "pin";

            // Check if user liked pin to toggle like button style
            const likedClass = pin.user_liked > 0 ? "liked" : "";

            div.innerHTML = `
              <img src="${pin.image_url}" alt="${pin.title}" loading="lazy" />
              <p>${pin.title}</p>
              <div class="pin-actions">
                <button class="btn btn-like ${likedClass}" data-id="${pin.id}">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="18" viewBox="0 0 24 24" width="18">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 1.01 4.5 2.09C13.09 4.01 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  <span>${pin.like_count}</span>
                </button>
                <a href="${pin.image_url}" download class="btn btn-download" title="Download Image">
                  ⬇️ Download
                </a>
              </div>
            `;
            grid.appendChild(div);
          });

          // Attach like button events
          document.querySelectorAll('.btn-like').forEach(btn => {
            btn.onclick = () => {
              const pinId = btn.getAttribute('data-id');
              fetch('like_pin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `pin_id=${pinId}`
              })
              .then(res => res.json())
              .then(res => {
                if (res.liked) {
                  btn.classList.add('liked');
                  let countSpan = btn.querySelector('span');
                  countSpan.textContent = parseInt(countSpan.textContent) + 1;
                } else {
                  btn.classList.remove('liked');
                  let countSpan = btn.querySelector('span');
                  countSpan.textContent = parseInt(countSpan.textContent) - 1;
                }
              });
            };
          });

          loading = false;
          page++;
        })
        .catch(() => { loading = false; });
    }

    // Initial load
    loadPins();

    // Filter button click
    document.getElementById('filterBtn').addEventListener('click', () => {
      page = 1;
      const tags = document.getElementById('tagFilter').value;
      loadPins(tags);
    });

    // Infinite scroll trigger
    window.addEventListener('scroll', () => {
      if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 100)) {
        loadPins(document.getElementById('tagFilter').value);
      }
    });
  </script>

</body>
</html>
