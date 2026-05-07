<?php
require 'config.php';

// ПРОВЕРКА АВТОРИЗАЦИИ
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель - Заявки</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Админ-панель управления заявками</h1>
    <p>Здравствуйте, <?= htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8') ?>!</p>
    <p><a href="admin_logout.php">Выйти</a></p>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Заявка успешно удалена</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    
    <h2>Список заявок</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Язык</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // БЕЗОПАСНЫЙ ЗАПРОС С JOIN
            $sql = "SELECT a.id, a.name, a.email, pl.name as language_name 
                    FROM application a
                    LEFT JOIN application_language al ON a.id = al.application_id
                    LEFT JOIN programming_language pl ON al.language_id = pl.id
                    ORDER BY a.id DESC";
            
            $stmt = $pdo->query($sql);
            while ($row = $stmt->fetch()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['language_name'] ?? 'Не указан', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="POST" action="admin_delete.php" style="display:inline" 
                              onsubmit="return confirm('Удалить заявку №<?= $row['id'] ?>?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" style="background:red; color:white; border:none; padding:5px 10px; cursor:pointer;">
                                Удалить
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
