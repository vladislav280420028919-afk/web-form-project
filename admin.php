<?php
session_start();
require_once 'config.php';

// Проверка авторизации админа (сессионная)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Обработка удаления
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM application_language WHERE application_id = ?");
    $stmt->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM application WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php?msg=deleted");
    exit;
}

// Обработка редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = (int)$_POST['edit_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $birth_date = trim($_POST['birth_date']);
    $gender = trim($_POST['gender']);
    $biography = trim($_POST['biography']);
    $selected_langs = $_POST['languages'] ?? [];

    $errors = [];
    if (empty($full_name)) $errors[] = "Имя обязательно";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Неверный email";
    if (empty($birth_date)) $errors[] = "Дата рождения обязательна";
    if (!in_array($gender, ['male', 'female', 'other'])) $errors[] = "Некорректный пол";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE application SET full_name = ?, email = ?, phone = ?, birth_date = ?, gender = ?, biography = ?, is_edited = 1 WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $birth_date, $gender, $biography, $edit_id]);

        // Обновляем языки
        $stmt = $pdo->prepare("DELETE FROM application_language WHERE application_id = ?");
        $stmt->execute([$edit_id]);

        $stmtLang = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
        foreach ($selected_langs as $lang_id) {
            $stmtLang->execute([$edit_id, $lang_id]);
        }

        header("Location: admin.php?msg=updated");
        exit;
    }
}

// Получаем все языки
$allLanguages = $pdo->query("SELECT * FROM programming_language ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Получаем все заявки с их языками
$query = "
    SELECT a.id, a.full_name, a.email, a.phone, a.birth_date, a.gender, a.biography,
           GROUP_CONCAT(pl.name SEPARATOR ', ') as languages_list
    FROM application a
    LEFT JOIN application_language al ON a.id = al.application_id
    LEFT JOIN programming_language pl ON al.language_id = pl.id
    GROUP BY a.id
    ORDER BY a.id DESC
";
$applications = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Статистика: сколько пользователей любят каждый язык
$statsQuery = "
    SELECT pl.name, COUNT(al.application_id) as count
    FROM programming_language pl
    LEFT JOIN application_language al ON pl.id = al.language_id
    GROUP BY pl.id
    ORDER BY count DESC
";
$stats = $pdo->query($statsQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1, h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 2px; font-size: 12px; cursor: pointer; border: none; }
        .edit-btn { background: #2196F3; color: white; }
        .delete-btn { background: #f44336; color: white; }
        .stat-box { background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-item { display: inline-block; margin: 0 15px 10px 0; padding: 5px 10px; background: #e3f2fd; border-radius: 5px; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 5% auto; padding: 20px; width: 500px; border-radius: 8px; max-height: 80%; overflow-y: auto; }
        .modal-content input, .modal-content select, .modal-content textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ddd; border-radius: 4px; }
        .modal-content label { font-weight: bold; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .logout { float: right; background: #f44336; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; }
        .logout:hover { background: #d32f2f; }
        .clearfix:after { content: ""; clear: both; display: table; }
        .lang-checkbox { width: auto !important; margin-right: 5px !important; }
        .lang-label { display: inline-block; margin-right: 15px; white-space: nowrap; }
    </style>
</head>
<body>
<div class="container">
    <div class="clearfix">
        <h1>Панель администратора</h1>
        <a href="admin_logout.php" class="logout">Выйти</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="success">
            <?php 
                if ($_GET['msg'] == 'deleted') echo "✓ Заявка успешно удалена";
                if ($_GET['msg'] == 'updated') echo "✓ Заявка успешно обновлена";
            ?>
        </div>
    <?php endif; ?>

    <h2>📊 Статистика по языкам программирования</h2>
    <div class="stat-box">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-item">
                <strong><?= htmlspecialchars($stat['name']) ?>:</strong> <?= $stat['count'] ?> пользователей
            </div>
        <?php endforeach; ?>
        <?php if (empty($stats)): ?>
            <p>Нет данных о языках</p>
        <?php endif; ?>
    </div>

    <h2>📝 Все заявки пользователей (всего: <?= count($applications) ?>)</h2>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Биография</th>
                    <th>Языки</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= $app['id'] ?></td>
                        <td><?= htmlspecialchars($app['full_name']) ?></td>
                        <td><?= htmlspecialchars($app['email']) ?></td>
                        <td><?= htmlspecialchars($app['phone']) ?></td>
                        <td><?= htmlspecialchars($app['birth_date']) ?></td>
                        <td>
                            <?php 
                                $genderMap = ['male' => 'Мужской', 'female' => 'Женский', 'other' => 'Другой'];
                                echo $genderMap[$app['gender']] ?? $app['gender'];
                            ?>
                        </td>
                        <td style="max-width: 200px;"><?= htmlspecialchars(mb_substr($app['biography'], 0, 50)) ?><?= strlen($app['biography']) > 50 ? '...' : '' ?></td>
                        <td><?= htmlspecialchars($app['languages_list'] ?: 'Не указаны') ?></td>
                        <td style="white-space: nowrap;">
                            <button class="btn edit-btn" onclick='openEditModal(<?= json_encode($app) ?>)'>✏ Редактировать</button>
                            <a href="?delete=<?= $app['id'] ?>" class="btn delete-btn" onclick="return confirm('Удалить заявку №<?= $app['id'] ?> от <?= htmlspecialchars($app['full_name']) ?>?')">🗑 Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Модальное окно редактирования -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Редактирование заявки</h3>
            <form method="POST">
                <input type="hidden" name="edit_id" id="edit_id">
                
                <label>ФИО:</label>
                <input type="text" name="full_name" id="edit_full_name" required>
                
                <label>Email:</label>
                <input type="email" name="email" id="edit_email" required>
                
                <label>Телефон:</label>
                <input type="text" name="phone" id="edit_phone" required>
                
                <label>Дата рождения:</label>
                <input type="date" name="birth_date" id="edit_birth_date" required>
                
                <label>Пол:</label>
                <select name="gender" id="edit_gender">
                    <option value="male">Мужской</option>
                    <option value="female">Женский</option>
                    <option value="other">Другой</option>
                </select>
                
                <label>Биография:</label>
                <textarea name="biography" id="edit_biography" rows="3"></textarea>
                
                <label>Языки программирования:</label>
                <div id="languages_list">
                    <?php foreach ($allLanguages as $lang): ?>
                        <label class="lang-label">
                            <input type="checkbox" name="languages[]" value="<?= $lang['id'] ?>" class="lang-checkbox" data-lang-id="<?= $lang['id'] ?>">
                            <?= htmlspecialchars($lang['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <br>
                <button type="submit" class="btn edit-btn">💾 Сохранить</button>
                <button type="button" class="btn delete-btn" onclick="closeModal()">Отмена</button>
            </form>
        </div>
    </div>
</div>

<script>
let currentLanguages = [];

function openEditModal(app) {
    document.getElementById('edit_id').value = app.id;
    document.getElementById('edit_full_name').value = app.full_name;
    document.getElementById('edit_email').value = app.email;
    document.getElementById('edit_phone').value = app.phone;
    document.getElementById('edit_birth_date').value = app.birth_date;
    document.getElementById('edit_gender').value = app.gender;
    document.getElementById('edit_biography').value = app.biography || '';
    
    // Загружаем текущие языки этой заявки
    fetch(`get_application_langs.php?id=${app.id}`)
        .then(res => res.json())
        .then(data => {
            currentLanguages = data;
            // Снимаем все галочки
            document.querySelectorAll('.lang-checkbox').forEach(cb => cb.checked = false);
            // Отмечаем нужные
            document.querySelectorAll('.lang-checkbox').forEach(cb => {
                if (currentLanguages.includes(parseInt(cb.value))) {
                    cb.checked = true;
                }
            });
        })
        .catch(error => console.error('Ошибка:', error));
    
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeModal();
    }
}
</script>
</body>
</html>
