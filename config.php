<?php
// config.php
$host = 'localhost';
$dbname = 'u82420';
$user = 'u82420';
$pass = '1644474';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Функция для проверки HTTP-авторизации админа
function authenticateAdmin($pdo) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('HTTP/1.0 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Area"');
        echo 'Доступ запрещён. Нужна авторизация.';
        exit;
    }

    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    $stmt = $pdo->prepare("SELECT password_hash FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        header('HTTP/1.0 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Area"');
        echo 'Неверный логин или пароль.';
        exit;
    }

    return true;
}
?>
