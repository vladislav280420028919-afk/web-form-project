<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админ-панель</title>
    <style>
        .error { color: red; }
        form { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Вход в админ-панель</h1>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="admin_check.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        
        <div>
            <label>Логин:</label>
            <input type="text" name="username" required>
        </div>
        <br>
        
        <div>
            <label>Пароль:</label>
            <input type="password" name="password" required>
        </div>
        <br>
        
        <button type="submit">Войти</button>
    </form>
</body>
</html>
