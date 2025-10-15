<?php 
    session_start();

    $userImages = [];
    $imagesFile = 'user-images.txt';

    // 1. Проверяем, авторизован ли пользователь
    if (isset($_SESSION['username']) && file_exists($imagesFile)) {
        $username = $_SESSION['username'];
        // Читаем все строки из файла
        $lines = file($imagesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Разделяем строку на имя пользователя и имя файла
            list($storedUsername, $fileName) = explode(':', $line, 2);

            // 2. Если имя пользователя совпадает, добавляем имя файла в массив
            if ($storedUsername === $username) {
                $userImages[] = htmlspecialchars($fileName);
            }
        }
    }
?>
<html lang="ru">
    <head>
        <title>Simple project php</title>
        <meta charset="utf8">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #EEE;
                margin: 0;
                padding: 20px;
                font-size: 18px
            }
            .container {
                max-width: 25em;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            .container__buttons {
                display: flex;
                gap: 0.5rem;
            }
            h1 {
                font-size: 3rem;
            }
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
            .user-photos {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 20px;
            }
            .user-photos img {
                width: 150px;
                height: 150px;
                object-fit: cover;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <?php if(isset($_SESSION['username'])) :?>
            <p>Добро пожаловать, **<?php echo htmlspecialchars($_SESSION['username'])?>**</p>
            <div class="container__buttons">
                <a href="upload.php">Загрузить фото</a>
                <a href="logout.php">Выход</a>
            </div>

            <hr>
            <h3>Ваши загруженные фотографии:</h3>
            <?php if (!empty($userImages)): ?>
                <div class="user-photos">
                    <?php foreach ($userImages as $imageName): ?>
                        <img src="uploads/<?php echo $imageName; ?>" alt="Фото пользователя">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>У вас пока нет загруженных фотографий.</p>
            <?php endif; ?>

        <?php else: ?>
        <div class="container">
            <h1>Simple project php</h1>
            <div class="container__buttons">
                <a href="/login.php">Вход</a>
                <a href="/register.php">Регистрация</a>
            </div>
        </div>
        <?php endif; ?>
    </body>
</html>