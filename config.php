<?php
session_start();

// ОТКЛЮЧАЕМ ВЫВОД ОШИБОК (Information Disclosure)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// PDO подключение
$host = 'localhost';
$db   = 'u82420';
$user = 'u82420';
$pass = '1644474';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // ЗАЩИТА ОТ SQL INJECTION
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    die("Ошибка подключения к базе данных.");
}

// CSRF ФУНКЦИИ
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
