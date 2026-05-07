<?php
session_start();
require_once 'config.php';

// Функция валидации
function validateForm($data, $isEdit = false) {
    $errors = [];
    
    $full_name = trim($data['full_name'] ?? '');
    if (empty($full_name)) {
        $errors['full_name'] = 'ФИО обязательно';
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = 'ФИО слишком короткое';
    }
    
    $phone = trim($data['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Телефон обязателен';
    } elseif (!preg_match('/^[\+\d\s\(\)-]{10,20}$/', $phone)) {
        $errors['phone'] = 'Неверный формат телефона';
    }
    
    $email = trim($data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email обязателен';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Неверный email';
    }
    
    if (empty($data['contract_agreed'])) {
        $errors['contract_agreed'] = 'Необходимо согласие на обработку данных';
    }
    
    return $errors;
}

// Функция генерации случайного пароля
function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

// Функция генерации уникального логина
function generateUniqueLogin($pdo, $base = 'user') {
    $login = $base . rand(1000, 9999);
    $stmt = $pdo->prepare("SELECT id FROM application WHERE login = ?");
    $stmt->execute([$login]);
    while ($stmt->fetch()) {
        $login = $base . rand(1000, 9999);
        $stmt->execute([$login]);
    }
    return $login;
}

// Получаем данные
$postData = $_POST;
$errors = validateForm($postData, isset($_SESSION['user_id']));

if (!empty($errors)) {
    $_SESSION['form_data'] = $postData;
    $_SESSION['form_errors'] = $errors;
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();
    
    $full_name = trim($postData['full_name']);
    $phone = trim($postData['phone']);
    $email = trim($postData['email']);
    $birth_date = !empty($postData['birth_date']) ? $postData['birth_date'] : null;
    $gender = $postData['gender'] ?? null;
    $biography = trim($postData['biography'] ?? '');
    $contract_agreed = isset($postData['contract_agreed']) ? 1 : 0;
    $languages = $postData['languages'] ?? [];
    
    // Если пользователь авторизован - ОБНОВЛЯЕМ
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE application SET 
            full_name = ?, phone = ?, email = ?, birth_date = ?, 
            gender = ?, biography = ?, contract_agreed = ?, is_edited = 1 
            WHERE id = ?");
        $stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $biography, $contract_agreed, $_SESSION['user_id']]);
        $appId = $_SESSION['user_id'];
        
        // Удаляем старые языки
        $pdo->prepare("DELETE FROM application_language WHERE application_id = ?")->execute([$appId]);
    } 
    // Иначе - новая заявка с генерацией логина/пароля
    else {
        $login = generateUniqueLogin($pdo);
        $plainPassword = generatePassword();
        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO application 
            (login, password_hash, full_name, phone, email, birth_date, gender, biography, contract_agreed) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$login, $passwordHash, $full_name, $phone, $email, $birth_date, $gender, $biography, $contract_agreed]);
        $appId = $pdo->lastInsertId();
        
        // Сохраняем логин/пароль в сессию для показа 1 раз
        $_SESSION['generated_login'] = $login;
        $_SESSION['generated_password'] = $plainPassword;
    }
    
    // Сохраняем языки
    foreach ($languages as $langId) {
        $stmtLang = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
        $stmtLang->execute([$appId, $langId]);
    }
    
    $pdo->commit();
    
    // Если не авторизован - сохраняем в куки (как в задании 4)
    if (!isset($_SESSION['user_id'])) {
        setcookie('last_application', json_encode(['full_name' => $full_name, 'email' => $email]), time() + 86400, '/');
    }
    
    header('Location: index.php');
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['form_errors']['db'] = 'Ошибка сохранения: ' . $e->getMessage();
    $_SESSION['form_data'] = $postData;
    header('Location: index.php');
}
exit;
?>

