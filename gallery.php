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
        .empty-state {
            padding: var(--space-10);
            text-align: center;
            color: var(--text-secondary);
            font-size: 18px;
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
            <p class="empty-state">No photos in your gallery yet. We’ll add them soon.</p>
        <?php else: ?>
            <div class="gallery">
                <?php foreach ($images as $img): ?>
                    <div class="gallery-item">
                        <img src="image.php?file=<?php echo urlencode($img); ?>" alt="<?php echo htmlspecialchars($img); ?>" loading="lazy">
                        <button type="button" class="btn-fav <?php echo isset($selected_set[$img]) ? 'selected' : ''; ?>" data-image="<?php echo htmlspecialchars($img); ?>" aria-label="Toggle favorite">♥</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
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
    </script>
</body>
</html>
