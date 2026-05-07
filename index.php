<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подача заявки на обучение</title>
    <style>
        .error { color: red; }
        .success { color: green; }
        form { margin: 20px 0; }
        label { display: inline-block; width: 150px; }
    </style>
</head>
<body>
    <h1>Форма подачи заявки</h1>
    
    <!-- ЗАЩИТА ОТ XSS: экранирование вывода -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            Ошибка: <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="success">
            Заявка успешно отправлена! Спасибо.
        </div>
    <?php endif; ?>

    <form method="POST" action="process.php">
        <!-- CSRF ТОКЕН -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        
        <div>
            <label>Имя:</label>
            <input type="text" name="name" required>
        </div>
        <br>
        
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <br>
        
        <div>
            <label>Язык программирования:</label>
            <select name="language_id">
                <?php
                // БЕЗОПАСНЫЙ ЗАПРОС (PDO prepare не нужен для SELECT без переменных)
                $stmt = $pdo->query("SELECT id, name FROM programming_language");
                while ($row = $stmt->fetch()):
                ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <br>
        
        <button type="submit">Отправить заявку</button>
    </form>
</body>
</html>
