<?php
require 'config.php';

// ПРОВЕРКА АВТОРИЗАЦИИ
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    http_response_code(403);
    die("Доступ запрещён");
}

// CSRF ПРОВЕРКА
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    http_response_code(403);
    die("Ошибка CSRF");
}

// ПОЛУЧЕНИЕ ID
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: admin.php?error=Неверный ID');
    exit;
}

// ========== SQL INJECTION ЗАЩИТА (PREPARED STATEMENT) ==========
try {
    // Удаляем связь с языками
    $stmt1 = $pdo->prepare("DELETE FROM application_language WHERE application_id = ?");
    $stmt1->execute([$id]);
    
    // Удаляем заявку
    $stmt2 = $pdo->prepare("DELETE FROM application WHERE id = ?");
    $stmt2->execute([$id]);
    
    header('Location: admin.php?deleted=1');
} catch (PDOException $e) {
    error_log("Delete error: " . $e->getMessage());
    header('Location: admin.php?error=Ошибка при удалении');
}
?>
