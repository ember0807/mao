<?php
    session_start();

    // 1. Проверка авторизации
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }

    $error = null;
    $message = null;

    if($_SERVER['REQUEST_METHOD'] === "POST") {
        // 2. Проверка, был ли файл фактически загружен
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            
            // 3. Устанавливаем целевую директорию для загрузки
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Создать директорию, если её нет
            }
            
            $fileInfo = pathinfo($_FILES['file']['name']);
            $fileType = strtolower($fileInfo['extension']);
            
            // 4. Генерируем уникальное имя файла
            $fileName = uniqid() . '.' . $fileType;
            $targetFile = $uploadDir . $fileName;

            // 5. Проверка типа файла
            $allowedTypes = ['jpeg', 'jpg', 'png', 'gif'];
            if (in_array($fileType, $allowedTypes)) {
                // 6. Перемещение загруженного файла
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                    // 7. Сохранение данных в user-images.txt
                    $dataToSave = $_SESSION['username'] . ":" . $fileName . "\n";
                    file_put_contents('user-images.txt', $dataToSave, FILE_APPEND);
                    $message = "Файл успешно загружен: " . htmlspecialchars($fileName);
                } else {
                    $error = "Ошибка при перемещении загруженного файла.";
                }
            } else {
                $error = "Разрешены файлы только JPG, JPEG, PNG, GIF.";
            }
        } elseif (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
             // Обработка других ошибок загрузки, кроме отсутствия файла
             $error = "Ошибка загрузки файла. Код: " . $_FILES['file']['error'];
        } else {
             // Если форма отправлена, но файл не выбран (хотя в форме стоит required)
             $error = "Выберите файл для загрузки.";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Загрузка Файла</title>
        <meta charset="UTF-8">
    </head>
    <body>
        <div class="container">
            <h2>Загрузка Фото</h2>
            
            <?php if(isset($error) && $error):?>
                <p style="color: red;"><?php echo htmlspecialchars($error);?></p>
            <?php endif;?>
            
            <?php if(isset($message) && $message):?>
                <p style="color: green;"><?php echo htmlspecialchars($message);?></p>
            <?php endif;?>
            
            <form method="POST" enctype="multipart/form-data"> 
                <label for="file">Выберите фото:</label><br>
                <input type="file" id="file" name="file" accept="image/jpeg,image/png,image/gif" required><br><br>
                <button type="submit">Загрузить</button>
            </form>
            
            <p><a href="index.php">Вернуться к фотографиям</a></p>
            <p><a href="logout.php">Выход</a></p>
        </div>
    </body>
</html>