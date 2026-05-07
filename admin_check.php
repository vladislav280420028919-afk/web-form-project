<?php
require 'config.php';

// CSRF ПРОВЕРКА
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    die("Ошибка CSRF");
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// БЕЗОПАСНЫЙ ЗАПРОС (prepared statement)
$stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

// Проверка пароля (если в БД хеш, иначе прямое сравнение)
if ($admin && $password === $admin['password']) {
    $_SESSION['admin_logged'] = true;
    $_SESSION['admin_username'] = $admin['username'];
    header('Location: admin.php');
} else {
    header('Location: admin_login.php?error=Неверный логин или пароль');
}
?>
