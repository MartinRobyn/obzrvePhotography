<?php
session_start();
require_once __DIR__ . '/private/config.php';

if (empty($_SESSION['client_id']) || empty($_SESSION['client_folder'])) {
    header('Location: client-login.html');
    exit;
}

$client_id     = $_SESSION['client_id'];
$client_folder = $_SESSION['client_folder'];
$base          = rtrim(UPLOADS_ROOT, '/\\') . '/' . $client_folder;
$allowed       = ['jpg', 'jpeg', 'png', 'webp'];
$images        = [];

if (is_dir($base)) {
    foreach (scandir($base) as $f) {
        if ($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $images[] = $f;
        }
    }
}
sort($images);

// Load current selections for this client
$selected_set = [];
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('SELECT image FROM selections WHERE client_id = ?');
    $stmt->execute([$client_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected_set[$row['image']] = true;
    }
} catch (PDOException $e) {
    // no DB or table missing — selections just won't show as selected
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your gallery — Photono</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink-950: #0B0B0C;
            --ink-900: #111114;
            --paper-50: #FFFFFF;
            --paper-100: #F6F6F6;
            --ash-200: #E6E6E6;
            --ash-500: #8F8F8F;
            --ash-700: #4A4A4A;
            --text-primary: var(--ink-950);
            --text-secondary: var(--ash-700);
            --border: var(--ash-200);
            --font-display: 'Anton', Impact, sans-serif;
            --font-text: 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
            --radius-sm: 6px;
            --space-4: 16px;
            --space-5: 20px;
            --space-6: 24px;
            --space-8: 40px;
            --space-10: 56px;
            --duration-base: 180ms;
            --easing-standard: cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-text);
            font-size: 16px;
            line-height: 1.55;
            color: var(--text-primary);
            background: var(--paper-100);
            min-height: 100vh;
            padding: var(--space-8);
        }
        .container { max-width: 1240px; margin: 0 auto; }
        .header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-6);
            margin-bottom: var(--space-10);
            padding-bottom: var(--space-6);
            border-bottom: 1px solid var(--border);
        }
        .gallery-title {
            font-family: var(--font-display);
            font-size: 32px;
            line-height: 1.1;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .header-meta {
            font-size: 14px;
            color: var(--text-secondary);
        }
        .header-meta a {
            color: var(--ink-950);
            text-decoration: none;
            font-weight: 500;
        }
        .header-meta a:hover { text-decoration: underline; }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: var(--space-6);
        }
        .gallery-item {
            position: relative;
            aspect-ratio: 4/3;
            background: var(--ash-200);
            overflow: hidden;
            border-radius: var(--radius-sm);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .btn-fav {
            position: absolute;
            top: var(--space-4);
            right: var(--space-4);
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            color: var(--ash-500);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: color var(--duration-base) var(--easing-standard), background var(--duration-base) var(--easing-standard);
        }
        .btn-fav:hover { background: #fff; color: var(--ink-950); }
        .btn-fav.selected { color: #B42318; background: rgba(255,255,255,0.95); }
        .gallery-item { cursor: pointer; }
        .empty-state {
            padding: var(--space-10);
            text-align: center;
            color: var(--text-secondary);
            font-size: 18px;
        }

        /* ─── Lightbox ─── */
        .lightbox-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.92);
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: var(--space-6);
        }
        .lightbox-overlay.active { display: flex; }

        .lightbox-img {
            max-width: 90vw;
            max-height: 80vh;
            object-fit: contain;
            border-radius: var(--radius-sm);
            user-select: none;
            -webkit-user-drag: none;
        }

        .lightbox-close {
            position: absolute;
            top: var(--space-5);
            right: var(--space-5);
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background var(--duration-base) var(--easing-standard);
        }
        .lightbox-close:hover { background: rgba(255,255,255,0.3); }

        .lightbox-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border: none;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            color: #fff;
            font-size: 26px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background var(--duration-base) var(--easing-standard);
        }
        .lightbox-arrow:hover { background: rgba(255,255,255,0.3); }
        .lightbox-prev { left: var(--space-5); }
        .lightbox-next { right: var(--space-5); }

        .lightbox-toolbar {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            margin-top: var(--space-5);
        }

        .lightbox-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: #fff;
            color: var(--ink-950);
            border: none;
            border-radius: var(--radius-sm);
            font-family: var(--font-text);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background var(--duration-base) var(--easing-standard);
        }
        .lightbox-download:hover { background: var(--ash-200); }
        .lightbox-download svg { width: 18px; height: 18px; }

        .lightbox-counter {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .lightbox-arrow { width: 40px; height: 40px; font-size: 20px; }
            .lightbox-prev { left: 8px; }
            .lightbox-next { right: 8px; }
            .lightbox-img { max-width: 96vw; max-height: 75vh; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1 class="gallery-title">Your gallery</h1>
            <p class="header-meta">
                Logged in as <strong><?php echo htmlspecialchars($client_id); ?></strong>
                — <a href="logout.php">Log out</a>
                — <a href="index.html">Back to Photono</a>
            </p>
        </header>

        <?php if (empty($images)): ?>
            <p class="empty-state">No photos in your gallery yet. We'll add them soon.</p>
        <?php else: ?>
            <div class="gallery">
                <?php foreach ($images as $idx => $img): ?>
                    <div class="gallery-item" data-index="<?php echo $idx; ?>">
                        <img src="image.php?file=<?php echo urlencode($img); ?>" alt="<?php echo htmlspecialchars($img); ?>" loading="lazy">
                        <button type="button" class="btn-fav <?php echo isset($selected_set[$img]) ? 'selected' : ''; ?>" data-image="<?php echo htmlspecialchars($img); ?>" aria-label="Toggle favorite">♥</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Lightbox -->
    <div class="lightbox-overlay" id="lightbox">
        <button class="lightbox-close" id="lb-close" aria-label="Close">✕</button>
        <button class="lightbox-arrow lightbox-prev" id="lb-prev" aria-label="Previous">&#8249;</button>
        <button class="lightbox-arrow lightbox-next" id="lb-next" aria-label="Next">&#8250;</button>
        <img class="lightbox-img" id="lb-img" src="" alt="">
        <div class="lightbox-toolbar">
            <a class="lightbox-download" id="lb-download" href="#" download>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
            </a>
            <span class="lightbox-counter" id="lb-counter"></span>
        </div>
    </div>

    <script>
    (function() {
        /* ─── Image list from PHP ─── */
        var imageList = <?php echo json_encode(array_values($images), JSON_UNESCAPED_SLASHES); ?>;
        var currentIndex = 0;

        /* ─── Lightbox elements ─── */
        var overlay  = document.getElementById('lightbox');
        var lbImg    = document.getElementById('lb-img');
        var lbClose  = document.getElementById('lb-close');
        var lbPrev   = document.getElementById('lb-prev');
        var lbNext   = document.getElementById('lb-next');
        var lbDl     = document.getElementById('lb-download');
        var lbCount  = document.getElementById('lb-counter');

        function showImage(idx) {
            if (idx < 0) idx = imageList.length - 1;
            if (idx >= imageList.length) idx = 0;
            currentIndex = idx;
            var file = imageList[idx];
            lbImg.src = 'image.php?file=' + encodeURIComponent(file);
            lbImg.alt = file;
            lbDl.href = 'image.php?file=' + encodeURIComponent(file) + '&download=1';
            lbCount.textContent = (idx + 1) + ' / ' + imageList.length;
        }

        function openLightbox(idx) {
            showImage(idx);
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            lbImg.src = '';
        }

        /* Click on gallery thumbnails */
        document.querySelectorAll('.gallery-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                /* Don't open lightbox when clicking the favorite heart */
                if (e.target.closest('.btn-fav')) return;
                var idx = parseInt(this.getAttribute('data-index'), 10);
                openLightbox(idx);
            });
        });

        lbClose.addEventListener('click', closeLightbox);
        lbPrev.addEventListener('click', function() { showImage(currentIndex - 1); });
        lbNext.addEventListener('click', function() { showImage(currentIndex + 1); });

        /* Click backdrop to close */
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeLightbox();
        });

        /* Keyboard navigation */
        document.addEventListener('keydown', function(e) {
            if (!overlay.classList.contains('active')) return;
            if (e.key === 'Escape')      closeLightbox();
            if (e.key === 'ArrowLeft')    showImage(currentIndex - 1);
            if (e.key === 'ArrowRight')   showImage(currentIndex + 1);
        });

        /* Swipe support for mobile */
        var touchStartX = 0;
        overlay.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        overlay.addEventListener('touchend', function(e) {
            var diff = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(diff) > 50) {
                if (diff < 0) showImage(currentIndex + 1);
                else showImage(currentIndex - 1);
            }
        }, { passive: true });

        /* ─── Favorite toggle (unchanged) ─── */
        document.querySelectorAll('.btn-fav').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var img = this.getAttribute('data-image');
                var self = this;
                fetch('selection.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'image=' + encodeURIComponent(img) + '&action=toggle'
                })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.ok) self.classList.toggle('selected', d.selected);
                })
                .catch(function() {});
            });
        });
    })();
    </script>
</body>
</html>
