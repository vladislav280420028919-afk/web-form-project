<?php
// Заголовки для работы с Cookies
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Функция для чтения cookie
function getCookieValue($name, $default = '') {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
}

// Читаем ошибки из cookies
$errors = [];
$error_cookie = getCookieValue('form_errors');
if ($error_cookie) {
    $errors = json_decode($error_cookie, true) ?: [];
    setcookie('form_errors', '', time() - 3600, '/');
}

// Читаем сохранённые данные
$savedData = [];
$saved_cookie = getCookieValue('form_data');
if ($saved_cookie) {
    $savedData = json_decode($saved_cookie, true) ?: [];
}

// Функции для отображения
function hasError($field, $errors) {
    return isset($errors[$field]) ? 'error-field' : '';
}

function showError($field, $errors) {
    if (isset($errors[$field])) {
        return '<span class="error-message">⚠ ' . htmlspecialchars($errors[$field]) . '</span>';
    }
    return '';
}

function getFieldValue($field, $savedData) {
    if (isset($_GET[$field])) {
        return htmlspecialchars($_GET[$field]);
    }
    if (isset($savedData[$field])) {
        return htmlspecialchars($savedData[$field]);
    }
    return '';
}

function isOptionSelected($field, $value, $savedData) {
    if (isset($_GET[$field]) && is_array($_GET[$field])) {
        return in_array($value, $_GET[$field]) ? 'selected' : '';
    }
    if (isset($savedData[$field]) && is_array($savedData[$field])) {
        return in_array($value, $savedData[$field]) ? 'selected' : '';
    }
    return '';
}

function isChecked($field, $value, $savedData) {
    if (isset($_GET[$field])) {
        return $_GET[$field] == $value ? 'checked' : '';
    }
    if (isset($savedData[$field])) {
        return $savedData[$field] == $value ? 'checked' : '';
    }
    return '';
}

$languages = [
    1 => 'PHP', 2 => 'Python', 3 => 'Java', 4 => 'JavaScript',
    5 => 'C++', 6 => 'C#', 7 => 'Ruby', 8 => 'Go',
    9 => 'Swift', 10 => 'Kotlin', 11 => 'TypeScript', 12 => 'Rust'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Задание 4 - Валидация формы</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #4a90e2; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        .required:after { content: " *"; color: red; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="date"], select, textarea { 
            width: 100%; padding: 8px 10px; border: 2px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        .error-field { border-color: red !important; background-color: #ffe6e6; }
        .error-message { color: red; font-size: 12px; margin-top: 5px; display: block; }
        .field-note { color: #666; font-size: 11px; margin-top: 5px; }
        .radio-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
        select[multiple] { height: 120px; }
        button { background: #4a90e2; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #357abd; }
        .error-summary { background: #fee; border-left: 4px solid red; padding: 10px 15px; margin-bottom: 20px; border-radius: 4px; }
        .error-summary ul { margin: 10px 0 0 20px; }
        .success-message { background: #e8f5e9; border-left: 4px solid green; padding: 10px 15px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Задание 4 - Валидация формы с Cookies</h1>
        
        <?php if (!empty($errors)): ?>
        <div class="error-summary">
            <strong>⚠ Обнаружены ошибки:</strong>
            <ul>
                <?php foreach ($errors as $field => $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success-message">
            ✓ Форма успешно отправлена! Данные сохранены в Cookies.
        </div>
        <?php endif; ?>
        
        <form action="process.php" method="POST">
            <div class="form-group">
                <label class="required">ФИО</label>
                <input type="text" name="full_name" value="<?php echo getFieldValue('full_name', $savedData); ?>" class="<?php echo hasError('full_name', $errors); ?>">
                <div class="field-note">📌 Допустимы: русские/английские буквы, пробелы, дефис. 2-100 символов</div>
                <?php echo showError('full_name', $errors); ?>
            </div>
            
            <div class="form-group">
                <label class="required">Телефон</label>
                <input type="text" name="phone" value="<?php echo getFieldValue('phone', $savedData); ?>" class="<?php echo hasError('phone', $errors); ?>">
                <div class="field-note">📌 Формат: +7 (912) 345-67-89 или 89123456789</div>
                <?php echo showError('phone', $errors); ?>
            </div>
            
            <div class="form-group">
                <label class="required">Email</label>
                <input type="email" name="email" value="<?php echo getFieldValue('email', $savedData); ?>" class="<?php echo hasError('email', $errors); ?>">
                <div class="field-note">📌 Формат: name@domain.ru (латиница, цифры, точки, дефис)</div>
                <?php echo showError('email', $errors); ?>
            </div>
            
            <div class="form-group">
                <label class="required">Дата рождения</label>
                <input type="date" name="birth_date" value="<?php echo getFieldValue('birth_date', $savedData); ?>" class="<?php echo hasError('birth_date', $errors); ?>">
                <div class="field-note">📌 Возраст от 18 до 100 лет</div>
                <?php echo showError('birth_date', $errors); ?>
            </div>
            
            <div class="form-group">
                <label class="required">Пол</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" <?php echo isChecked('gender', 'male', $savedData); ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo isChecked('gender', 'female', $savedData); ?>> Женский</label>
                </div>
                <?php echo showError('gender', $errors); ?>
            </div>
            
            <div class="form-group">
                <label class="required">Языки программирования</label>
                <select name="languages[]" multiple class="<?php echo hasError('languages', $errors); ?>">
                    <?php foreach ($languages as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo isOptionSelected('languages', $id, $savedData); ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="field-note">📌 Выберите минимум один язык (Ctrl+клик для множественного выбора)</div>
                <?php echo showError('languages', $errors); ?>
            </div>
            
            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="4"><?php echo getFieldValue('biography', $savedData); ?></textarea>
                <div class="field-note">📌 Необязательно, максимум 500 символов</div>
            </div>
            
            <div class="form-group">
                <label class="required">
                    <input type="checkbox" name="contract_agreed" value="1" <?php echo (isset($savedData['contract_agreed']) && $savedData['contract_agreed'] == 1) ? 'checked' : ''; ?>>
                    Я согласен на обработку персональных данных
                </label>
                <?php echo showError('contract_agreed', $errors); ?>
            </div>
            
            <button type="submit">Отправить</button>
        </form>
    </div>
</body>
</html>
