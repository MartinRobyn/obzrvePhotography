<?php
session_start();
require_once __DIR__ . '/private/config.php';

header('Content-Type: application/json');

if (empty($_SESSION['client_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

$client_id = $_SESSION['client_id'];
$image = isset($_POST['image']) ? trim($_POST['image']) : (isset($_GET['image']) ? trim($_GET['image']) : '');
$image = basename($image);
$action = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : 'toggle');

if ($image === '') {
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false]);
    exit;
}

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
$selected = (bool) $stmt->fetchColumn();

echo json_encode(['ok' => true, 'selected' => $selected]);
