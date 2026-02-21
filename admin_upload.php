<?php
session_start();
require_once __DIR__ . '/private/config.php';

$uploaded = [];
$upload_error = '';

// Admin login check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === ADMIN_PASSWORD) {
        $_SESSION['admin_ok'] = true;
        header('Location: admin_upload.php');
        exit;
    }
    $upload_error = 'Wrong password.';
}

if (!empty($_GET['logout']) && !empty($_SESSION['admin_ok'])) {
    unset($_SESSION['admin_ok']);
    header('Location: client-login.html');
    exit;
}

if (empty($_SESSION['admin_ok'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin — Photono</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root { --ink-950: #0B0B0C; --paper-50: #FFFFFF; --paper-100: #F6F6F6; --ash-400: #B9B9B9; --ash-700: #4A4A4A; --danger: #B42318; --font-display: 'Anton', sans-serif; --font-text: 'Inter', sans-serif; --space-4: 16px; --space-6: 24px; --space-8: 40px; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: var(--font-text); background: var(--paper-100); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-6); }
            .card { background: var(--paper-50); padding: var(--space-8); max-width: 360px; width: 100%; box-shadow: 0 6px 24px rgba(0,0,0,0.08); }
            h1 { font-family: var(--font-display); font-size: 24px; text-transform: uppercase; margin-bottom: var(--space-6); }
            label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ash-700); margin-bottom: var(--space-4); }
            input[type="password"] { width: 100%; padding: var(--space-4); border: 2px solid var(--ash-400); font-size: 16px; }
            button { margin-top: var(--space-6); padding: var(--space-4) var(--space-6); background: var(--ink-950); color: #fff; border: none; font-weight: 600; cursor: pointer; }
            .err { color: var(--danger); font-size: 14px; margin-top: var(--space-4); }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Admin login</h1>
            <form method="POST">
                <label>Password</label>
                <input type="password" name="admin_pass" required autocomplete="current-password">
                <?php if ($upload_error): ?><p class="err"><?php echo htmlspecialchars($upload_error); ?></p><?php endif; ?>
                <button type="submit">Enter</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos']) && isset($_POST['client_folder'])) {
    $client_folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['client_folder']);
    if ($client_folder !== '') {
        $base = rtrim(UPLOADS_ROOT, '/\\') . '/' . $client_folder;
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size = 15 * 1024 * 1024; // 15 MB

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
}

// Get list of client folders for dropdown (from DB or from uploads dir)
$client_folders = [];
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SELECT folder FROM clients ORDER BY folder');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $client_folders[] = $row['folder'];
    }
} catch (PDOException $e) {}
$uploads_base = rtrim(UPLOADS_ROOT, '/\\');
if (is_dir($uploads_base)) {
    foreach (scandir($uploads_base) as $d) {
        if ($d === '.' || $d === '..' || !is_dir($uploads_base . '/' . $d)) continue;
        if (!in_array($d, $client_folders)) $client_folders[] = $d;
    }
}
sort($client_folders);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload photos — Photono Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink-950: #0B0B0C; --paper-50: #FFFFFF; --paper-100: #F6F6F6;
            --ash-200: #E6E6E6; --ash-400: #B9B9B9; --ash-700: #4A4A4A;
            --success: #067647; --font-display: 'Anton', sans-serif; --font-text: 'Inter', sans-serif;
            --space-4: 16px; --space-5: 20px; --space-6: 24px; --space-8: 40px; --radius-sm: 6px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-text); background: var(--paper-100); padding: var(--space-8); }
        .container { max-width: 640px; margin: 0 auto; }
        h1 { font-family: var(--font-display); font-size: 28px; text-transform: uppercase; margin-bottom: var(--space-6); }
        .card { background: var(--paper-50); padding: var(--space-8); margin-bottom: var(--space-6); box-shadow: 0 6px 24px rgba(0,0,0,0.08); border-radius: var(--radius-sm); }
        .form-group { margin-bottom: var(--space-6); }
        .form-group label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ash-700); margin-bottom: var(--space-4); }
        .form-group input[type="text"], .form-group select { width: 100%; padding: var(--space-4); border: 2px solid var(--ash-200); font-size: 16px; }
        .btn { padding: var(--space-4) var(--space-6); background: var(--ink-950); color: #fff; border: none; font-weight: 600; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #111; }
        .success { color: var(--success); font-size: 14px; margin-top: var(--space-4); }
        .meta { font-size: 14px; color: var(--ash-700); margin-top: var(--space-6); }
        .meta a { color: var(--ink-950); }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload client photos</h1>
        <div class="card">
            <?php if (!empty($uploaded)): ?>
                <p class="success">Uploaded <?php echo count($uploaded); ?> file(s): <?php echo htmlspecialchars(implode(', ', $uploaded)); ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Client folder</label>
                    <input type="text" name="client_folder" list="client-folders" placeholder="e.g. client_smith" required value="<?php echo isset($_POST['client_folder']) ? htmlspecialchars($_POST['client_folder']) : ''; ?>">
                    <?php if (!empty($client_folders)): ?>
                    <datalist id="client-folders">
                        <?php foreach ($client_folders as $f): ?>
                            <option value="<?php echo htmlspecialchars($f); ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Photos (JPG, PNG, WebP — max 15 MB each)</label>
                    <input type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp">
                </div>
                <button type="submit" class="btn">Upload</button>
            </form>
        </div>
        <p class="meta"><a href="client-login.html">Client login</a> · <a href="index.html">Site</a> · <a href="admin_upload.php?logout=1">Log out admin</a></p>
    </div>
</body>
</html>
