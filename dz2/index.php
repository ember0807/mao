<?php
// index.php: Главная страница приложения. Показывает галерею изображений для авторизованных пользователей или форму входа/регистрации.

// Логический блок: Инициализация сессии
// Назначение: Запускает или возобновляет сессию для хранения данных пользователя (например, username, csrf_token).
// Параметры: Нет.
session_start();

// Логический блок: Подключение к базе данных
// Назначение: Создает соединение с MySQL через PDO для выполнения запросов.
// Параметры PDO:
//   DSN: 'mysql:host=db;dbname=photo_app' - Хост (db - имя сервиса в docker-compose), имя базы (photo_app).
//   Пользователь: 'root' - Имя пользователя MySQL.
//   Пароль: 'rootpassword' - Пароль пользователя.
//   Опции: PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION - Включает режим выброса исключений при ошибках.
try {
    $pdo = new PDO('mysql:host=db;dbname=photo_app', 'root', 'rootpassword', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // Обработка ошибки: Выводит сообщение об ошибке подключения и останавливает выполнение.
    // Параметры: $e->getMessage() - Текст ошибки PDO.
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

<html lang="ru">
<head>
    <title>Simple project php</title>
    <meta charset="utf8">
    <link rel="stylesheet" href="styles.css">
    <!-- Встроенные стили: Дополняют styles.css для оформления главной страницы. -->
    <style>
        /* body: Основные стили страницы (шрифт, фон, отступы). */
        body {
            font-family: Arial, sans-serif;
            background-color: #EEE;
            margin: 0;
            padding: 20px;
            font-size: 18px;
        }
        /* container: Контейнер для контента с максимальной шириной и flex-расположением. */
        .container {
            max-width: 25em;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        /* container__buttons: Контейнер для кнопок с flex и отступами. */
        .container__buttons {
            display: flex;
            gap: 0.5rem;
        }
        /* h1: Заголовок для неавторизованных пользователей. */
        h1 {
            font-size: 3rem;
        }
        /* a: Стили ссылок-кнопок (цвет, фон, центрирование). */
        a {
            flex: 1;
            color: #FFFFFF;
            background-color: blue;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 0.25rem;
            text-decoration: none;
        }
        /* photo-gallery: Flex-контейнер для отображения изображений в галерее. */
        .photo-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        /* photo-gallery img: Стили для изображений (адаптивность, рамка). */
        .photo-gallery img {
            max-width: 100%;
            height: auto;
            border: 1px solid #555;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
<?php
// Логический блок: Проверка авторизации
// Назначение: Определяет, авторизован ли пользователь, и отображает соответствующий контент.
// Параметры: $_SESSION['username'] - Имя пользователя из сессии (если есть).
if (isset($_SESSION['username'])): ?>
    <div class="container">
        <!-- Приветствие: Выводит имя пользователя, защищенное от XSS-атак. -->
        <!-- Параметры: htmlspecialchars($_SESSION['username']) - Экранирует специальные символы. -->
        <p>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <div class="container__buttons">
            <a href="upload.php">Upload Photo</a>
            <a href="logout.php">Logout</a>
        </div>
        <h2>Your Photos</h2>
        <?php
        // Логический блок: Получение изображений
        // Назначение: Извлекает пути к изображениям текущего пользователя из базы данных.
        // SQL: SELECT image_path FROM user_images WHERE username = ? ORDER BY uploaded_at DESC
        // Назначение SQL: Выбирает пути к изображениям для указанного пользователя, сортируя по времени загрузки (новые первыми).
        // Параметры SQL: ? - Плейсхолдер для имени пользователя.
        // Параметры PDO:
        //   prepare(): Подготавливает SQL-запрос.
        //   execute([$_SESSION['username']]): Связывает имя пользователя.
        //   fetchAll(PDO::FETCH_ASSOC): Возвращает массив ассоциативных массивов.
        $stmt = $pdo->prepare("SELECT image_path FROM user_images WHERE username = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$_SESSION['username']]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Проверка наличия изображений
        if ($images): ?>
            <!-- Галерея: Отображает изображения в flex-контейнере. -->
            <div class="photo-gallery">
                <?php foreach ($images as $image): ?>
                    <!-- Изображение: Выводит путь с защитой от XSS. -->
                    <!-- Параметры: htmlspecialchars($image['image_path']) - Экранирует путь к файлу. -->
                    <img src="Uploads/<?php echo htmlspecialchars($image['image_path']); ?>" alt="User uploaded photo">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Сообщение: Если изображений нет. -->
            <p>No photos uploaded yet.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Неавторизованный вид: Показывает заголовок и кнопки для входа/регистрации. -->
    <div class="container">
        <h1>Simple project php</h1>
        <div class="container__buttons">
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
        </div>
    </div>
<?php endif; ?>
</body>
</html>