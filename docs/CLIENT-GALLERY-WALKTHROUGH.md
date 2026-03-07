# Client Photo Gallery System — Step-by-Step Walkthrough

This guide **walks you through** building a secure client gallery on Hostinger (PHP + MySQL), from zero to a working system. Do each phase in order; each step tells you exactly what to do and how to know you’re done.

---

## Before You Start

**You’ll need:**

- Hostinger hosting with PHP and MySQL
- FTP or File Manager access
- A code editor
- About 1–2 hours for the first working version (login + gallery)

**Reality tip:** Start with login + gallery only. Add uploads, selections, and polish later.

---

# Phase 1 — Login System

**Goal:** A client can enter an ID + access code and get a “logged in” session.

---

## Step 1.1 — Create the database and clients table

**What you’ll do:** Set up MySQL and one table for clients.

1. In **Hostinger hPanel** → **Databases** → **MySQL Databases**, create a new database (e.g. `photono_gallery`).
2. Create a MySQL user and assign it to that database. Note: database name, username, password.
3. In **phpMyAdmin** (or MySQL client), run:

```sql
CREATE TABLE clients (
  client_id   VARCHAR(50) PRIMARY KEY,
  access_code VARCHAR(64) NOT NULL,
  folder      VARCHAR(100) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example client (change the code in production!)
INSERT INTO clients (client_id, access_code, folder) VALUES
('smith', 'X7kP92', 'client_smith');
```

**Checkpoint:** In phpMyAdmin you see table `clients` with one row (e.g. smith / X7kP92 / client_smith).

---

## Step 1.2 — Create a config file (and keep it safe)

**What you’ll do:** One PHP file that holds database credentials, so you never put passwords in other scripts.

1. On the server, create a folder **above or beside** `public_html` if possible (e.g. `private` or `config`). If Hostinger only gives you `public_html`, put the file there but **never** link to it from the site; we’ll protect it with `.htaccess` later.
2. Create `config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'photono_gallery');   // your DB name
define('DB_USER', 'your_db_user');      // your DB user
define('DB_PASS', 'your_db_password');  // your DB password
```

**Checkpoint:** You have one `config.php` with the four constants set. No other file should repeat the password.

---

## Step 1.3 — Build the login page (HTML)

**What you’ll do:** A simple form that sends client ID and access code to a PHP script.

1. In `public_html`, create `client-login.html` (or `login.html`):

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Gallery — Login</title>
</head>
<body>
  <h1>Client Gallery</h1>
  <p>Enter your client ID and access code.</p>
  <form action="login.php" method="POST">
    <label>Client ID <input type="text" name="client_id" required></label><br>
    <label>Access code <input type="password" name="access_code" required></label><br>
    <button type="submit">Enter gallery</button>
  </form>
</body>
</html>
```

**Checkpoint:** Opening `client-login.html` in the browser shows the form. Submitting it will go to `login.php` (we create that next).

---

## Step 1.4 — Build the login script (PHP)

**What you’ll do:** PHP checks the code against the database, starts a session if valid, redirects to the gallery.

1. In `public_html`, create `login.php`:

```php
<?php
session_start();
require_once __DIR__ . '/../config.php';  // adjust path to your config.php

$client_id    = trim($_POST['client_id'] ?? '');
$access_code  = trim($_POST['access_code'] ?? '');

if (!$client_id || !$access_code) {
  header('Location: client-login.html?error=missing');
  exit;
}

$pdo = new PDO(
  'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
  DB_USER,
  DB_PASS,
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $pdo->prepare('SELECT folder FROM clients WHERE client_id = ? AND access_code = ?');
$stmt->execute([$client_id, $access_code]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  header('Location: client-login.html?error=invalid');
  exit;
}

$_SESSION['client_id'] = $client_id;
$_SESSION['client_folder'] = $row['folder'];
$_SESSION['logged_in_at'] = time();

header('Location: gallery.php');
exit;
```

**Important:** Change the `require_once` path so it points to your real `config.php` (e.g. if config is inside `public_html`, use `__DIR__ . '/config.php'`).

**Checkpoint:** Submitting the form with `smith` / `X7kP92` redirects to `gallery.php`. Wrong code redirects back to login with `?error=invalid`.

---

# Phase 2 — Session protection and folder structure

**Goal:** Only logged-in clients can see the gallery; their images live in a clear folder structure.

---

## Step 2.1 — Block direct access to gallery

**What you’ll do:** If there’s no session, send the user to the login page.

1. At the **very top** of `gallery.php` (before any HTML), add:

```php
<?php
session_start();
if (empty($_SESSION['client_id']) || empty($_SESSION['client_folder'])) {
  header('Location: client-login.html');
  exit;
}
$client_id     = $_SESSION['client_id'];
$client_folder = $_SESSION['client_folder'];
?>
```

**Checkpoint:** Visiting `gallery.php` without logging in first redirects to `client-login.html`.

---

## Step 2.2 — Create the image folder structure

**What you’ll do:** One folder per client; keep it outside direct URL access if possible.

1. Create a folder for uploads **outside** `public_html` if your host allows, e.g.:
   - `private/uploads/client_smith/`
   - `private/uploads/client_jones/`
2. If you can only use `public_html`, create:
   - `public_html/private/uploads/client_smith/`
   and we’ll protect `private` with `.htaccess` in the next step.
3. Put 1–2 test images in `client_smith` (e.g. `img1.jpg`, `img2.jpg`) so we can test the gallery.

**Checkpoint:** You have at least one client folder with at least one image. Path matches what you stored in the DB (`folder` = `client_smith`).

---

## Step 2.3 — Disable directory listing and protect private (if under public_html)

**What you’ll do:** Prevent anyone from browsing folders and (if applicable) block direct access to `private`.

1. In `public_html`, create or edit `.htaccess`:

```apache
Options -Indexes
```

2. If your uploads are in `public_html/private/`, add inside that folder another `.htaccess`:

```apache
Options -Indexes
Deny from all
```

**Checkpoint:** Visiting `yoursite.com/private/` or `yoursite.com/private/uploads/` should be forbidden or show 403.

---

# Phase 3 — Gallery display

**Goal:** Logged-in clients see their photos in a grid; images are served through PHP so direct URLs don’t work.

---

## Step 3.1 — Decide where the upload root lives (path in PHP)

**What you’ll do:** Define one constant in PHP that points to the upload root so all scripts use the same path.

1. In `config.php` add (adjust to your real path):

```php
// Path to upload root (no trailing slash). Examples:
// __DIR__ . '/uploads'           if private/uploads is next to config.php
// '/home/youruser/private/uploads'  if you have shell path
define('UPLOADS_ROOT', __DIR__ . '/uploads');
```

2. Ensure the client folder is under that root, e.g. `UPLOADS_ROOT . '/' . $client_folder` = path to that client’s images.

**Checkpoint:** You know the exact path on the server to `client_smith` (e.g. `.../uploads/client_smith`).

---

## Step 3.2 — Serve images through PHP (so they’re not guessable)

**What you’ll do:** Images are loaded via `image.php?file=filename.jpg`; the script checks session and client folder, then outputs the file.

1. Create `image.php` in `public_html`:

```php
<?php
session_start();
require_once __DIR__ . '/../config.php';  // adjust to your config

if (empty($_SESSION['client_id']) || empty($_SESSION['client_folder'])) {
  http_response_code(403);
  exit('Forbidden');
}

$file = basename($_GET['file'] ?? '');
if ($file === '') {
  http_response_code(400);
  exit('Bad request');
}

$client_folder = $_SESSION['client_folder'];
$base = rtrim(UPLOADS_ROOT, '/') . '/' . $client_folder;
$path = realpath($base . '/' . $file);

if ($path === false || strpos($path, $base) !== 0) {
  http_response_code(404);
  exit('Not found');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
$mime = $mimes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
```

**Checkpoint:** When logged in as smith, visiting `image.php?file=img1.jpg` shows the image. Logging out or changing `file` to another client’s image returns 403/404.

---

## Step 3.3 — Build the gallery page (HTML + PHP)

**What you’ll do:** List images from the client’s folder and show them in a grid using `image.php`.

1. Create or replace `gallery.php` so it has the session check at the top (Step 2.1), then:

```php
<?php
// ... session check and $client_id, $client_folder ...
require_once __DIR__ . '/../config.php';

$base = rtrim(UPLOADS_ROOT, '/') . '/' . $client_folder;
$allowed = ['jpg', 'jpeg', 'png', 'webp'];
$images = [];

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your gallery</title>
  <style>
    .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    .gallery img { width: 100%; height: 200px; object-fit: cover; display: block; }
  </style>
</head>
<body>
  <h1>Your gallery</h1>
  <p>Logged in as <?php echo htmlspecialchars($client_id); ?> — <a href="logout.php">Log out</a></p>
  <div class="gallery">
    <?php foreach ($images as $img): ?>
      <img src="image.php?file=<?php echo urlencode($img); ?>" alt="<?php echo htmlspecialchars($img); ?>">
    <?php endforeach; ?>
  </div>
</body>
</html>
```

2. Create `logout.php`:

```php
<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: client-login.html');
exit;
```

**Checkpoint:** After login you see a grid of images; each image loads via `image.php`. Log out link works.

---

# Phase 4 — Admin upload panel

**Goal:** You (admin) can upload images into a chosen client folder; page is password-protected.

---

## Step 4.1 — Protect the admin page with a password

**What you’ll do:** Simple admin auth so only you can open the upload page.

1. In `config.php` add:

```php
define('ADMIN_PASSWORD', 'choose-a-strong-password-here');
```

2. Create `admin_upload.php` and at the top (before any HTML):

```php
<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pass'])) {
  if ($_POST['admin_pass'] === ADMIN_PASSWORD) {
    $_SESSION['admin_ok'] = true;
    header('Location: admin_upload.php');
    exit;
  }
}
if (empty($_SESSION['admin_ok'])) {
  ?>
  <!DOCTYPE html>
  <html><body>
  <form method="POST">
    <label>Admin password <input type="password" name="admin_pass" required></label>
    <button type="submit">Enter</button>
  </form>
  </body></html>
  <?php
  exit;
}
// ... rest of upload form and logic below ...
```

**Checkpoint:** Visiting `admin_upload.php` shows a password form; correct password shows the rest of the page.

---

## Step 4.2 — Upload form and handling

**What you’ll do:** Form to pick client folder and upload multiple images; only allow image types and sane sizes.

1. Add to `admin_upload.php` (after the admin check), the upload handler:

```php
$uploaded = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos']) && isset($_POST['client_folder'])) {
  $client_folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['client_folder']);
  $base = rtrim(UPLOADS_ROOT, '/') . '/' . $client_folder;
  if (!is_dir($base)) {
    mkdir($base, 0755, true);
  }
  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  $max_size = 10 * 1024 * 1024; // 10 MB

  foreach ($_FILES['photos']['name'] as $i => $name) {
    if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) continue;
    if ($_FILES['photos']['size'][$i] > $max_size) continue;
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    $safe = date('Ymd_His') . '_' . $i . '_' . $safe;
    $dest = $base . '/' . $safe;
    if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $dest)) {
      $uploaded[] = $safe;
    }
  }
}
```

2. Add the form (same file):

```php
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Upload photos</title></head>
<body>
  <h1>Upload client photos</h1>
  <?php if (!empty($uploaded)): ?>
    <p>Uploaded: <?php echo htmlspecialchars(implode(', ', $uploaded)); ?></p>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <label>Client folder (e.g. client_smith) <input type="text" name="client_folder" required></label><br>
    <label>Photos <input type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp"></label><br>
    <button type="submit">Upload</button>
  </form>
</body>
</html>
```

**Checkpoint:** You can select a client folder, choose multiple images, upload; new files appear in that folder and in the client gallery.

---

# Phase 5 — Selection system (favorites / picks)

**Goal:** Client can mark images as “favorites”; we store that (DB or file).

---

## Step 5.1 — Store selections (simple: database)

**What you’ll do:** One table for “client X selected image Y”.

1. In MySQL run:

```sql
CREATE TABLE selections (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  client_id  VARCHAR(50) NOT NULL,
  image      VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_per_client_image (client_id, image)
);
```

**Checkpoint:** Table `selections` exists.

---

## Step 5.2 — Toggle selection via AJAX

**What you’ll do:** Clicking a “heart” or “select” on an image sends a request to save or remove the selection.

1. Create `selection.php` (in `public_html`):

```php
<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
if (empty($_SESSION['client_id'])) {
  http_response_code(403);
  echo json_encode(['ok' => false]);
  exit;
}

$client_id = $_SESSION['client_id'];
$image = basename($_POST['image'] ?? $_GET['image'] ?? '');
$action = $_POST['action'] ?? $_GET['action'] ?? 'toggle'; // add | remove | toggle

if ($image === '') {
  echo json_encode(['ok' => false]);
  exit;
}

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);

if ($action === 'add') {
  $pdo->prepare('INSERT IGNORE INTO selections (client_id, image) VALUES (?, ?)')->execute([$client_id, $image]);
} elseif ($action === 'remove') {
  $pdo->prepare('DELETE FROM selections WHERE client_id = ? AND image = ?')->execute([$client_id, $image]);
} else {
  $stmt = $pdo->prepare('SELECT 1 FROM selections WHERE client_id = ? AND image = ?');
  $stmt->execute([$client_id, $image]);
  if ($stmt->fetch()) {
    $pdo->prepare('DELETE FROM selections WHERE client_id = ? AND image = ?')->execute([$client_id, $image]);
  } else {
    $pdo->prepare('INSERT IGNORE INTO selections (client_id, image) VALUES (?, ?)')->execute([$client_id, $image]);
  }
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM selections WHERE client_id = ? AND image = ?');
$stmt->execute([$client_id, $image]);
echo json_encode(['ok' => true, 'selected' => (bool)$stmt->fetchColumn()]);
```

2. In `gallery.php`, add a “heart” or “select” control per image and a small script:

```html
<div class="gallery">
  <?php foreach ($images as $img): ?>
    <div class="gallery-item">
      <img src="image.php?file=<?php echo urlencode($img); ?>" alt="">
      <button type="button" class="btn-select" data-image="<?php echo htmlspecialchars($img); ?>">♥</button>
    </div>
  <?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.btn-select').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var img = this.dataset.image;
    fetch('selection.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'image=' + encodeURIComponent(img) + '&action=toggle'
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.classList.toggle('selected', d.selected);
    });
  });
});
</script>
```

**Checkpoint:** Clicking the heart toggles selection; refreshing the page you could show “selected” state by loading current selections from DB (optional next step).

---

# Phase 6 — Security hardening

**What you’ll do:** Lock down a few things so the system is safer in production.

- **HTTPS:** In Hostinger, enable SSL for your domain so all traffic is HTTPS.
- **Session:** In PHP (or `.user.ini` / `php.ini`), set `session.cookie_httponly = 1` and `session.cookie_secure = 1` when on HTTPS.
- **Inputs:** You’re already using `basename()`, `realpath()`, and whitelisted extensions in `image.php` and uploads; keep that pattern everywhere.
- **.htaccess:** You already have `Options -Indexes` and (if needed) `Deny from all` for `private`.
- **Passwords:** Change the example client access code and admin password; consider hashing access codes in the DB (e.g. `password_hash` / `password_verify`) in a later iteration.

**Checkpoint:** Site runs over HTTPS; session cookies are HttpOnly (and Secure if possible); no directory listing; private folder not directly accessible.

---

# Phase 7 — UI polish (optional)

**Ideas you can add later:**

- “Download selected” button (zip or single-file download via PHP).
- Slideshow or lightbox for preview.
- Show “selected” state on load (query `selections` and add class to buttons).
- Compare mode (side-by-side).
- Watermark overlay toggle (e.g. low-opacity overlay for preview only).

---

# Quick reference — recommended order

| Order | What |
|-------|------|
| 1 | Login system (DB, config, login form, login.php) |
| 2 | Session protection + folder structure + .htaccess |
| 3 | Gallery display (image.php + gallery.php + logout) |
| 4 | Admin upload panel |
| 5 | Selection system (DB + selection.php + UI) |
| 6 | Security hardening |
| 7 | UI polish |

---

# Optional: “Ultra simple” selections (no database)

If you prefer not to use a table for selections, use a text file per client:

- Path: e.g. `private/selections/smith.txt`
- Each line = one selected filename.
- On toggle: PHP reads file, adds or removes the line, writes back. Lock the file if you expect concurrent use.

You can switch to the database version later when you want reporting or a “download selected” feature.

---

If you tell me whether you want a **simple** (minimal HTML/CSS) or **professional** (matches your Photono style) version, I can generate a starter bundle: login page, gallery page, upload panel, and the database schema in one go.
