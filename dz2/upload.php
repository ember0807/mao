<?php
// upload.php: Обрабатывает загрузку изображений, проверяет файлы и сохраняет их в базу.

// Логический блок: Инициализация сессии
// Назначение: Запускает сессию для проверки авторизации и хранения данных.
session_start();

// Логический блок: Проверка авторизации
// Назначение: Проверяет наличие username в сессии, иначе перенаправляет на login.
// Параметры: $_SESSION['username'] - Имя пользователя.
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Логический блок: Подключение к базе данных
// Назначение: Аналогично другим файлам, создает соединение через PDO.
try {
    $pdo = new PDO('mysql:host=db;dbname=photo_app', 'root', 'rootpassword', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Логический блок: Генерация CSRF-токена
// Назначение: Создает токен для защиты формы загрузки.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Логический блок: Обработка загрузки файла
// Назначение: Обрабатывает POST-запрос с файлом, валидирует и сохраняет файл и данные.
// Параметры: $_SERVER['REQUEST_METHOD'] - Метод запроса (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF: Сравнивает токен из формы.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Недействительный CSRF-токен.";
    } else {
        // Определение директории: Устанавливает путь для сохранения файлов.
        // Параметры: __DIR__ . "/uploads/" - Текущая директория + uploads.
        $targetDir = __DIR__ . "/uploads/";
        // Создание директории: Создает папку uploads, если она не существует.
        // Параметры: mkdir($targetDir, 0755, true) - Путь, права, рекурсивно.
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        // Тип файла: Получает расширение файла в нижнем регистре.
        // Параметры: pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION) - Имя файла.
        $fileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
        // Имя файла: Генерирует уникальное имя с помощью uniqid.
        // Параметры: uniqid() - Уникальный ID; $fileType - Расширение.
        $fileName = uniqid() . '.' . $fileType;
        $targetFile = $targetDir . $fileName;

        // Валидация размера: Проверяет, что файл не превышает 5MB.
        // Параметры: $_FILES["file"]["size"] - Размер файла в байтах.
        if ($_FILES["file"]["size"] > 5000000) {
            $error = "Файл слишком большой. Максимум 5MB.";
        }
        // Валидация типа: Проверяет, что файл имеет разрешенное расширение.
        // Параметры: in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']).
        elseif (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error = "Разрешены только файлы JPG, JPEG, PNG, GIF.";
        }
        // Проверка существования: Проверяет, не существует ли файл с таким именем.
        // Параметры: file_exists($targetFile).
        elseif (file_exists($targetFile)) {
            $error = "Файл с таким именем уже существует.";
        }
        // Перемещение файла: Сохраняет загруженный файл в папку uploads.
        // Параметры: move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile) - Временный путь и целевой.
        elseif (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            // Сохранение в базу: Вставляет данные об изображении.
            // SQL: INSERT INTO user_images (username, image_path) VALUES (?, ?)
            // Назначение SQL: Добавляет запись об изображении (имя пользователя и путь).
            // Параметры SQL: ? - Плейсхолдеры для username и image_path.
            // Параметры PDO: prepare(), execute([$_SESSION['username'], $fileName]).
            $stmt = $pdo->prepare("INSERT INTO user_images (username, image_path) VALUES (?, ?)");
            $stmt->execute([$_SESSION['username'], $fileName]);
            $message = "Файл успешно загружен!";
        } else {
            $error = "Ошибка при загрузке файла.";
        }

        // AJAX-ответ: Возвращает JSON для асинхронных запросов.
        // Параметры: header('Content-Type: application/json'); json_encode(['success', 'message']).
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => isset($message), 'message' => $message ?? $error]);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка фото</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Загрузка фото</h2>
    <?php if (isset($message)): ?>
        <!-- Успех: Выводит сообщение об успешной загрузке. -->
        <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <!-- Ошибка: Выводит сообщение об ошибке. -->
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <!-- Форма загрузки: Отправляет файл и csrf_token методом POST. -->
    <form id="uploadForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="file">Выберите фото</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Загрузить</button>
    </form>
    <p><a href="index.php">На главную</a></p>
</div>
<script>
    // Логический блок: Обработка формы через AJAX
    // Назначение: Перехватывает отправку формы, отправляет данные асинхронно и отображает результат.
    // Параметры: fetch('upload.php', {method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'}}).
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await response.json();
            const messageDiv = document.createElement('p');
            messageDiv.className = result.success ? 'success' : 'error';
            messageDiv.textContent = result.message;
            form.before(messageDiv);
            if (result.success) form.reset();
        } catch (error) {
            const messageDiv = document.createElement('p');
            messageDiv.className = 'error';
            messageDiv.textContent = 'Ошибка при загрузке файла.';
            form.before(messageDiv);
        }
    });
</script>
</body>
</html>