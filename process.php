<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Функция валидации
function validateForm($data) {
    $errors = [];
    
    // 1. ФИО
    $full_name = trim($data['full_name'] ?? '');
    if (empty($full_name)) {
        $errors['full_name'] = 'ФИО обязательно для заполнения';
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]{2,100}$/u', $full_name)) {
        $errors['full_name'] = 'ФИО должно содержать только буквы, пробелы и дефис. Длина: 2-100 символов';
    }
    
    // 2. Телефон
    $phone = trim($data['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^(\+7|8)[\s\-]?\(?[0-9]{3}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/', $phone)) {
        $errors['phone'] = 'Неверный формат телефона. Пример: +7 (912) 345-67-89 или 89123456789';
    }
    
    // 3. Email
    $email = trim($data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors['email'] = 'Неверный формат email. Пример: user@domain.ru';
    }
    
    // 4. Дата рождения
    $birth_date = $data['birth_date'] ?? '';
    if (empty($birth_date)) {
        $errors['birth_date'] = 'Дата рождения обязательна';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $birth_date);
        if (!$date || $date->format('Y-m-d') !== $birth_date) {
            $errors['birth_date'] = 'Неверный формат даты';
        } else {
            $today = new DateTime();
            $age = $today->diff($date)->y;
            if ($age < 18 || $age > 100) {
                $errors['birth_date'] = 'Возраст должен быть от 18 до 100 лет';
            }
        }
    }
    
    // 5. Пол
    $gender = $data['gender'] ?? '';
    if (empty($gender)) {
        $errors['gender'] = 'Выберите пол';
    } elseif (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Неверное значение пола';
    }
    
    // 6. Языки программирования
    $languages = $data['languages'] ?? [];
    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    } elseif (!is_array($languages)) {
        $errors['languages'] = 'Неверный формат данных';
    }
    
    // 7. Согласие
    $contract_agreed = $data['contract_agreed'] ?? '';
    if ($contract_agreed !== '1') {
        $errors['contract_agreed'] = 'Необходимо согласие на обработку данных';
    }
    
    return $errors;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Очищаем данные
$cleanedData = [];
foreach ($_POST as $key => $value) {
    if (is_array($value)) {
        $cleanedData[$key] = array_map('trim', $value);
    } else {
        $cleanedData[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

// Валидируем
$errors = validateForm($cleanedData);

// Сохраняем данные в Cookies на 1 год (всегда)
setcookie('form_data', json_encode($cleanedData), time() + 365*24*3600, '/');

// Если есть ошибки
if (!empty($errors)) {
    setcookie('form_errors', json_encode($errors), time() + 3600, '/');
    header('Location: index.php');
    exit;
}

// Сохранение в базу данных
try {
    $pdo = new PDO("mysql:host=localhost;dbname=u82420;charset=utf8mb4", "u82420", "1644474");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    // Вставляем в application
    $sql = "INSERT INTO application (full_name, phone, email, birth_date, gender, biography, contract_agreed, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $cleanedData['full_name'],
        $cleanedData['phone'],
        $cleanedData['email'],
        $cleanedData['birth_date'],
        $cleanedData['gender'],
        $cleanedData['biography'] ?? '',
        $cleanedData['contract_agreed']
    ]);
    
    $application_id = $pdo->lastInsertId();
    
    // Вставляем языки
    $sqlLang = "INSERT INTO application_language (application_id, language_id) VALUES (?, ?)";
    $stmtLang = $pdo->prepare($sqlLang);
    foreach ($cleanedData['languages'] as $lang_id) {
        $stmtLang->execute([$application_id, $lang_id]);
    }
    
    $pdo->commit();
    
    // Успех - удаляем ошибки
    setcookie('form_errors', '', time() - 3600, '/');
    header('Location: index.php?success=1');
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $errors['database'] = 'Ошибка базы данных: ' . $e->getMessage();
    setcookie('form_errors', json_encode($errors), time() + 3600, '/');
    header('Location: index.php');
    exit;
}
?>
