<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT language_id FROM application_language WHERE application_id = ?");
    $stmt->execute([$id]);
    $langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    header('Content-Type: application/json');
    echo json_encode($langs);
} else {
    echo json_encode([]);
}
?>
