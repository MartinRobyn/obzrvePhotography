<?php
session_start();
require_once __DIR__ . '/private/config.php';

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
$base = rtrim(UPLOADS_ROOT, '/\\') . '/' . $client_folder;
$full = $base . '/' . $file;
$path = realpath($full);
$baseReal = realpath($base);

if ($path === false || $baseReal === false || strpos($path, $baseReal) !== 0) {
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
