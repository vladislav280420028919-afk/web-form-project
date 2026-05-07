<?php
require 'config.php';

// ========== 1. ПРОВЕРКА CSRF ==========
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?error=Неправильный метод запроса');
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    header('Location: index.php?error=Ошибка безопасности (CSRF)');
    exit;
}

// ========== 2. ПОЛУЧЕНИЕ И ВАЛИДАЦИЯ ДАННЫХ ==========
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$language_id = (int)($_POST['language_id'] ?? 0);

if (empty($name) || empty($email)) {
    header('Location: index.php?error=Все поля обязательны');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?error=Некорректный email');
    exit;
}

// ========== 3. ЗАЩИТА ОТ SQL INJECTION (PREPARED STATEMENTS) ==========
try {
    // Вставляем заявку
    $stmt = $pdo->prepare("INSERT INTO application (name, email) VALUES (?, ?)");
    $stmt->execute([$name, $email]);
    $application_id = $pdo->lastInsertId();
    
    // Вставляем связь с языком
    $stmt2 = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
    $stmt2->execute([$application_id, $language_id]);
    
    header('Location: index.php?success=1');
    
} catch (PDOException $e) {
    error_log("Process error: " . $e->getMessage());
    header('Location: index.php?error=Ошибка при сохранении');
}
?>
