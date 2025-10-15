<?php
// login.php: Обрабатывает вход пользователя, проверяет учетные данные и управляет сессией.

// Логический блок: Инициализация сессии
// Назначение: Запускает или возобновляет сессию для хранения данных (username, csrf_token).
// Параметры: Нет.
session_start();

// Логический блок: Подключение к базе данных
// Назначение: Создает соединение с MySQL через PDO.
// Параметры PDO:
//   DSN: 'mysql:host=db;dbname=photo_app' - Хост (db), имя базы (photo_app).
//   Пользователь: 'root'.
//   Пароль: 'rootpassword'.
//   Опции: PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION.
try {
    $pdo = new PDO('mysql:host=db;dbname=photo_app', 'root', 'rootpassword', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // Обработка ошибки: Выводит сообщение и останавливает выполнение.
    // Параметры: $e->getMessage() - Текст ошибки.
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Логический блок: Генерация CSRF-токена
// Назначение: Создает случайный токен для защиты от CSRF-атак, если он еще не существует.
// Параметры:
//   random_bytes(32): Генерирует 32 байта случайных данных.
//   bin2hex(): Преобразует байты в hex-строку.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Логический блок: Обработка формы входа
// Назначение: Обрабатывает POST-запрос от формы входа, проверяет данные и выполняет вход.
// Параметры: $_SERVER['REQUEST_METHOD'] - Метод запроса (должен быть POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF: Сравнивает токен из формы с сессией.
    // Параметры: $_POST['csrf_token'] - Токен из формы.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Недействительный CSRF-токен.";
    } else {
        // Очистка ввода: Удаляет пробелы из username и password.
        // Параметры: trim($_POST['username']) - Удаляет пробелы.
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Валидация username: Проверяет, содержит ли имя только буквы и цифры.
        // Параметры: preg_match('/^[a-zA-Z0-9]+$/', $username) - Регулярное выражение.
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $error = "Имя пользователя должно содержать только буквы и цифры.";
        } else {
            // Проверка учетных данных: Извлекает хэш пароля из базы.
            // SQL: SELECT password FROM users WHERE username = ?
            // Назначение SQL: Выбирает хэш пароля для указанного пользователя.
            // Параметры SQL: ? - Плейсхолдер для username.
            // Параметры PDO:
            //   prepare(): Подготавливает запрос.
            //   execute([$username]): Связывает имя пользователя.
            //   fetch(): Возвращает ассоциативный массив или false.
            $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            // Проверка пароля: Сравнивает введенный пароль с хэшем.
            // Параметры: password_verify($password, $user['password']) - Введенный пароль и хэш.
            if ($user && password_verify($password, $user['password'])) {
                // Сохранение сессии: Сохраняет имя пользователя в сессии.
                // Параметры: $_SESSION['username'] = $username.
                $_SESSION['username'] = $username;
                // Перенаправление: На главную страницу после успешного входа.
                header("Location: index.php");
                exit;
            } else {
                $error = "Неверное имя пользователя или пароль.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Вход</h2>
    <?php if (isset($error)): ?>
        <!-- Ошибка: Выводит сообщение об ошибке, если есть. -->
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <!-- Форма входа: Отправляет username, password и csrf_token методом POST. -->
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="username">Имя пользователя</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Пароль</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Войти</button>
    </form>
    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</div>
</body>
</html>