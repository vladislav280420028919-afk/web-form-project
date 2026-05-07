<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Успешно</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; text-align: center; }
        .success { background: white; max-width: 500px; margin: 100px auto; padding: 30px; border-radius: 8px; }
        h1 { color: #4caf50; }
    </style>
</head>
<body>
    <div class="success">
        <h1>✓ Успешно!</h1>
        <p>Данные успешно сохранены в базе данных.</p>
        <p>Данные формы сохранены в Cookies на 1 год.</p>
        <a href="index.php">Вернуться к форме</a>
    </div>
</body>
</html>
