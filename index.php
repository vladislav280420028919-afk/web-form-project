<?php
session_start();
require_once 'config.php';

// Восстанавливаем данные из сессии (для неавторизованных)
$formData = $_SESSION['form_data'] ?? [];
$formErrors = $_SESSION['form_errors'] ?? [];

// Получаем список языков из БД
$stmt = $pdo->query("SELECT id, name FROM programming_language ORDER BY name");
$languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Если пользователь авторизован, подгружаем его данные
$userData = [];
$userLanguages = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM application WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Получаем его языки
    if ($userData) {
        $stmtLang = $pdo->prepare("SELECT language_id FROM application_language WHERE application_id = ?");
        $stmtLang->execute([$_SESSION['user_id']]);
        $userLanguages = $stmtLang->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Очищаем временные данные сессии
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Анкета</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 20px auto; padding: 20px; }
        .error { color: red; font-size: 14px; margin-top: 5px; }
        .field { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .success { color: green; background: #e0ffe0; padding: 10px; margin-bottom: 20px; }
        .auth-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; text-align: right; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="auth-info">
            ✅ Вы вошли как: <?= htmlspecialchars($userData['login'] ?? '') ?>
            <a href="logout.php">[Выйти]</a>
            <br>Редактируете заявку от <?= htmlspecialchars($userData['full_name'] ?? '') ?>
        </div>
    <?php else: ?>
        <div class="auth-info">
            🔐 Уже есть логин? <a href="login.php">Войти</a> для редактирования
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['generated_login'])): ?>
        <div class="success">
            <strong>✅ Ваша заявка сохранена!</strong><br>
            Логин: <strong><?= htmlspecialchars($_SESSION['generated_login']) ?></strong><br>
            Пароль: <strong><?= htmlspecialchars($_SESSION['generated_password']) ?></strong><br>
            <span style="font-size: 14px;">Сохраните эти данные для редактирования заявки.</span>
            <?php unset($_SESSION['generated_login'], $_SESSION['generated_password']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="process.php">
        <div class="field">
            <label>ФИО: *</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($userData['full_name'] ?? $formData['full_name'] ?? '') ?>">
            <?php if (isset($formErrors['full_name'])): ?>
                <div class="error"><?= $formErrors['full_name'] ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label>Телефон: *</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? $formData['phone'] ?? '') ?>">
            <?php if (isset($formErrors['phone'])): ?>
                <div class="error"><?= $formErrors['phone'] ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label>Email: *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? $formData['email'] ?? '') ?>">
            <?php if (isset($formErrors['email'])): ?>
                <div class="error"><?= $formErrors['email'] ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label>Дата рождения:</label>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($userData['birth_date'] ?? $formData['birth_date'] ?? '') ?>">
        </div>

        <div class="field">
            <label>Пол:</label>
            <select name="gender">
                <option value="">Не указан</option>
                <option value="male" <?= (($userData['gender'] ?? $formData['gender'] ?? '') == 'male') ? 'selected' : '' ?>>Мужской</option>
                <option value="female" <?= (($userData['gender'] ?? $formData['gender'] ?? '') == 'female') ? 'selected' : '' ?>>Женский</option>
            </select>
        </div>

        <div class="field">
            <label>Языки программирования:</label>
            <select name="languages[]" multiple size="5">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= $lang['id'] ?>" 
                        <?php 
                        if (in_array($lang['id'], $userLanguages)) echo 'selected';
                        elseif (isset($formData['languages']) && in_array($lang['id'], $formData['languages'])) echo 'selected';
                        ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Биография:</label>
            <textarea name="biography" rows="4"><?= htmlspecialchars($userData['biography'] ?? $formData['biography'] ?? '') ?></textarea>
        </div>

        <div class="field">
            <label>
                <input type="checkbox" name="contract_agreed" value="1" <?= (($userData['contract_agreed'] ?? $formData['contract_agreed'] ?? '') == 1) ? 'checked' : '' ?>>
                Согласен на обработку данных *
            </label>
            <?php if (isset($formErrors['contract_agreed'])): ?>
                <div class="error"><?= $formErrors['contract_agreed'] ?></div>
            <?php endif; ?>
        </div>

        <button type="submit"><?= isset($_SESSION['user_id']) ? 'Сохранить изменения' : 'Отправить' ?></button>
    </form>
</body>
</html>
