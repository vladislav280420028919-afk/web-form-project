<?php
session_start();
require_once 'config.php';

// Если уже авторизован - перенаправляем в админку
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT password_hash FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админ-панель</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Вход в админ-панель</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Логин" required autofocus>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <div class="info">
            Логин: admin<br>
            Пароль: admin123
        </div>
    </div>
</body>
</html>
