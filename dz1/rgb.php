<?php
// rgb.php

// Инициализация переменных для хранения значений RGB и сообщений об ошибках
$r = 0; 
$b = 0; 
$g = 0; 
$error_message = "";
$background_color = "rgb(0,0,0)"; // Черный по умолчанию
$text_color = "white";            // Белый по умолчанию

// Проверка, была ли нажата кнопка "Accept"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение значений из полей формы
    $r_input = filter_input(INPUT_POST, 'r', FILTER_VALIDATE_INT);
    $g_input = filter_input(INPUT_POST, 'g', FILTER_VALIDATE_INT);
    $b_input = filter_input(INPUT_POST, 'b', FILTER_VALIDATE_INT);

    // Проверяем, являются ли все значения целыми числами
    if ($r_input !== false && $r_input !== null && 
        $g_input !== false && $g_input !== null && 
        $b_input !== false && $b_input !== null) 
    {
        $r = $r_input;
        $g = $g_input;
        $b = $b_input;

        if ($r >= 0 && $r <= 255 && $g >= 0 && $g <= 255 && $b >= 0 && $b <= 255) {
            // Все значения валидны, формируем цвет
            $background_color = "rgb(" . $r . "," . $g . "," . $b . ")";
            $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
            $text_color = ($brightness > 128) ? "black" : "white";
        } else {
            $error_message = "Значения должны быть в диапазоне от 0 до 255.";
        }
    } else {
        $error_message = "Пожалуйста, введите корректные числовые значения.";
    }
} 
?>

<!DOCTYPE html>
<html>
<head>
    <title>RGB Color Changer</title>
    <style>
        #color_box {
            padding: 20px;
            font-size: 20px;
            display: inline-block; 
            min-height: 20px; 
            margin-top: 20px;
        }
    </style>
</head>
<body>

<p><a href="start.html">← На главную</a></p>

<h1>RGB Color Changer</h1>

<form method="post">
    <label for="r">R:</label>
    <input type="number" id="r" name="r" value="<?php echo htmlspecialchars($r); ?>" min="0" max="255" required>

    <label for="g">G:</label>
    <input type="number" id="g" name="g" value="<?php echo htmlspecialchars($g); ?>" min="0" max="255" required>

    <label for="b">B:</label>
    <input type="number" id="b" name="b" value="<?php echo htmlspecialchars($b); ?>" min="0" max="255" required>

    <input type="submit" value="Accept">
</form>

<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>

<span id="color_box" style="background-color: <?php echo htmlspecialchars($background_color); ?>; color: <?php echo htmlspecialchars($text_color); ?>;">
    Какой-то текст внутри span.
</span>

</body>
</html>