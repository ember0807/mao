<?php
// register.php: Обрабатывает регистрацию нового пользователя, валидирует данные и сохраняет в базу.

// Логический блок: Инициализация сессии
// Назначение: Запускает сессию для хранения данных (username, csrf_token).
// Параметры: Нет.
session_start();

// Логический блок: Подключение к базе данных
// Назначение: Создает соединение с MySQL через PDO.
// Параметры PDO: Аналогично login.php.
try {
    $pdo = new PDO('mysql:host=db;dbname=photo_app', 'root', 'rootpassword', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Логический блок: Генерация CSRF-токена
// Назначение: Создает токен для защиты от CSRF-атак.
// Параметры: random_bytes(32), bin2hex().
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Логический блок: Обработка формы регистрации
// Назначение: Обрабатывает POST-запрос, валидирует данные и добавляет пользователя в базу.
// Параметры: $_SERVER['REQUEST_METHOD'] - Метод запроса (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF: Сравнивает токен из формы с сессией.
    // Параметры: $_POST['csrf_token'].
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Недействительный CSRF-токен.";
    } else {
        // Очистка ввода: Удаляет пробелы.
        // Параметры: trim($_POST['username']), trim($_POST['password']).
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $email = trim($_POST['email']);

        // Валидация: Проверяет username на буквы/цифры и заполненность полей.
        // Параметры: preg_match('/^[a-zA-Z0-9]+$/', $username).
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $error = "Имя пользователя должно содержать только буквы и цифры.";
        } elseif (empty($username) || empty($password) || empty($email)) {
            $error = "Заполните все поля.";
        } else {
            // Проверка уникальности: Проверяет, существует ли username.
            // SQL: SELECT COUNT(*) FROM users WHERE username = ?
            // Назначение SQL: Подсчитывает количество записей с указанным username.
            // Параметры SQL: ? - Плейсхолдер для username.
            // Параметры PDO: prepare(), execute([$username]), fetchColumn().
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) 
            {
            $error = "Имя пользователя или Email уже заняты.";
            } else 
            {
                // Хэширование пароля: Создает хэш с помощью bcrypt.
                // Параметры: password_hash($password, PASSWORD_DEFAULT).
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                // Добавление пользователя: Вставляет данные в таблицу users.
                // SQL: INSERT INTO users (username, password) VALUES (?, ?)
                // Назначение SQL: Добавляет нового пользователя с именем и хэшем пароля.
                // Параметры SQL: ? - Плейсхолдеры для username и password.
                // Параметры PDO: prepare(), execute([$username, $passwordHash]).
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $passwordHash]);
                // Перенаправление: На страницу входа.
                header("Location: login.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Регистрация</h2>
    <?php if (isset($error)): ?>
        <!-- Ошибка: Выводит сообщение об ошибке. -->
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <!-- Форма регистрации: Отправляет username, password и csrf_token. -->
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="username">Имя пользователя</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Пароль</label>
         <input type="password" name="password" id="password" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
       
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>
</body>
</html>