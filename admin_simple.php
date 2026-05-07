<?php
require_once 'config.php';

echo "<h2>Тест 1: Проверка PDO</h2>";
try {
    $pdo->query("SELECT 1");
    echo "<p style='color:green'>✓ PDO работает</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ PDO ошибка: " . $e->getMessage() . "</p>";
}

echo "<h2>Тест 2: Проверка HTTP Auth переменных</h2>";
echo "<pre>";
echo "PHP_AUTH_USER: " . (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'НЕ УСТАНОВЛЕН') . "\n";
echo "PHP_AUTH_PW: " . (isset($_SERVER['PHP_AUTH_PW']) ? '***установлен***' : 'НЕ УСТАНОВЛЕН') . "\n";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "</pre>";

echo "<h2>Тест 3: Попытка авторизации вручную</h2>";
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $stmt = $pdo->prepare("SELECT password_hash FROM admin WHERE username = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<p>✓ Админ найден</p>";
        if (password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
            echo "<p style='color:green; font-size:20px;'>✓✓✓ ПАРОЛЬ ВЕРНЫЙ! ✓✓✓</p>";
            echo "<p>Вы вошли как: " . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . "</p>";
        } else {
            echo "<p style='color:red'>✗ Пароль неверный</p>";
            echo "<p>Введённый пароль: " . $_SERVER['PHP_AUTH_PW'] . "</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Логин не найден: " . $_SERVER['PHP_AUTH_USER'] . "</p>";
    }
} else {
    echo "<p style='color:orange'>⚠ HTTP-авторизация не запрошена. Отправляем заголовки...</p>";
    
    // Отправляем заголовок авторизации
    header('HTTP/1.0 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Test Admin Area"');
    echo "Пожалуйста, введите логин и пароль";
    exit;
}
?>
