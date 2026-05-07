<?php
require_once 'config.php';

echo "<h2>Структура таблиц в БД</h2>";

// Проверяем таблицу application
echo "<h3>Таблица application:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE application");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Проверяем таблицу programming_language
echo "<h3>Таблица programming_language:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE programming_language");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Проверяем таблицу application_language
echo "<h3>Таблица application_language:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE application_language");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Показываем пример данных из application
echo "<h3>Пример данных из application (первые 5 записей):</h3>";
$stmt = $pdo->query("SELECT * FROM application LIMIT 5");
echo "<table border='1' cellpadding='5'>";
echo "<tr>";
for ($i = 0; $i < $stmt->columnCount(); $i++) {
    $col = $stmt->getColumnMeta($i);
    echo "<th>" . $col['name'] . "</th>";
}
echo "</tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>" . htmlspecialchars($value) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
