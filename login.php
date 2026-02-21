<?php
session_start();
require_once __DIR__ . '/private/config.php';

$client_id   = trim($_POST['client_id'] ?? '');
$access_code = trim($_POST['access_code'] ?? '');

if (!$client_id || !$access_code) {
    header('Location: client-login.html?error=missing');
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    header('Location: client-login.html?error=config');
    exit;
}

$stmt = $pdo->prepare('SELECT folder FROM clients WHERE client_id = ? AND access_code = ?');
$stmt->execute([$client_id, $access_code]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header('Location: client-login.html?error=invalid');
    exit;
}

$_SESSION['client_id']     = $client_id;
$_SESSION['client_folder'] = $row['folder'];
$_SESSION['logged_in_at']  = time();

header('Location: gallery.php');
exit;
