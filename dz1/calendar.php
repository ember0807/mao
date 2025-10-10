<?php

/**
 * @param int $month Номер месяца (от 1 до 12).
 * @param int $year Год.
 * @return bool Возвращает true при успехе, false при некорректном month.
 */
function generateCalendar(int $month, int $year): bool {
    // 1. Валидация входных данных
    if ($month < 1 || $month > 12) {
        echo '<p class="error">Ошибка: Номер месяца должен быть в интервале от 1 до 12.</p>';
        return false;
    }

    // 2. Определение важных дат
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    // Используем 'F' для полного названия месяца (например, 'October')
    $monthName = date('F', $firstDayOfMonth);
    $numberDays = (int)date('t', $firstDayOfMonth);
    // День недели, с которого начинается месяц (1 - Пн, 7 - Вс)
    $startDayOfWeek = (int)date('N', $firstDayOfMonth); 
    $dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    // 3. Генерация HTML
    echo '<div class="calendar-container">';
    echo '<h2>' . $monthName . ' ' . $year . '</h2>';
    echo '<table>';
    
    // Шапка таблицы
    echo '<thead><tr>';
    foreach ($dayNames as $day) {
        $class = ($day == 'Сб' || $day == 'Вс') ? 'weekend-header' : '';
        echo '<th class="' . $class . '">' . $day . '</th>';
    }
    echo '</tr></thead>';

    // Тело таблицы
    echo '<tbody><tr>';

    // Смещение: пустые ячейки до первого дня месяца
    for ($i = 1; $i < $startDayOfWeek; $i++) {
        echo '<td></td>';
    }

    $currentDay = 1;
    $dayCount = $startDayOfWeek; 

    while ($currentDay <= $numberDays) {
        if ($dayCount > 7) {
            echo '</tr><tr>';
            $dayCount = 1;
        }
        
        // Определение класса для выходных
        $class = ($dayCount == 6 || $dayCount == 7) ? 'weekend-day' : '';

        echo '<td class="' . $class . '">' . $currentDay . '</td>';

        $currentDay++;
        $dayCount++;
    }

    // Завершение последней строки пустыми ячейками
    while ($dayCount <= 7) {
        echo '<td></td>';
        $dayCount++;
    }

    echo '</tr></tbody>';
    echo '</table>';
    echo '</div>'; 
    return true;
}

// ========== ЛОГИКА ОПРЕДЕЛЕНИЯ ДАТЫ И НАВИГАЦИИ ==========
// Текущее время сервера (используется как запасной вариант, если JS не сработает)
$currentMonth = (int)date('m'); 
$currentYear = (int)date('Y');

// 1. Получаем месяц
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
if ($month === NULL || $month === false || $month < 1 || $month > 12) {
    $month = $currentMonth;
}

// 2. Получаем год
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
if ($year === NULL || $year === false) {
    $year = $currentYear;
}

// Расчет предыдущего месяца и года
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

// Расчет следующего месяца и года
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивный Календарь PHP</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .calendar-container { width: 300px; margin: 20px auto; border: 1px solid #ccc; padding: 10px; background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #e0e0e0; color: #555; font-size: 14px; }
        td { font-size: 16px; height: 30px; }
        .weekend-header { color: #b30000; } 
        .weekend-day { background-color: #ffeaea; color: #b30000; font-weight: bold; }
        .error { color: red; font-weight: bold; text-align: center; }
        
        /* Стили навигации */
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 300px;
            margin: 0 auto;
            margin-bottom: 10px;
        }
        .nav-link {
            font-size: 24px;
            text-decoration: none;
            color: #007bff;
            padding: 0 10px;
        }
        .nav-link:hover {
            color: #0056b3;
        }
        .year-select-form {
            display: inline-block;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Если в URL УЖЕ есть параметры месяца/года (т.е. навигация уже была),
            // то мы не перезагружаем страницу локальным временем.
            if (urlParams.has('month') && urlParams.has('year')) {
                return;
            }

            const now = new Date();
            const localMonth = now.getMonth() + 1;
            const localYear = now.getFullYear();

            // Устанавливаем значения в скрытые поля и отправляем форму.
            document.getElementById('js-month').value = localMonth;
            document.getElementById('js-year').value = localYear;
            
            document.getElementById('local-date-form').submit();
        });
    </script>
</head>
<body>

<form id="local-date-form" method="get" style="display: none;">
    <input type="hidden" name="month" id="js-month">
    <input type="hidden" name="year" id="js-year">
</form>

<p><a href="start.html">← На главную</a></p>
<h1>Интерактивный Календарь</h1>

<div class="nav-container">
    
    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="nav-link">←</a>
    
    <form method="get" class="year-select-form">
        <input type="hidden" name="month" value="<?php echo $month; ?>">
        
        <select name="year" onchange="this.form.submit()">
            <?php 
            // Расширяем диапазон на 100 лет назад и 100 лет вперед
            $startYear = $currentYear - 100; 
            $endYear = $currentYear + 100;   
            
            for ($y = $startYear; $y <= $endYear; $y++) {
                $selected = ($y == $year) ? 'selected' : '';
                echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
            }
            ?>
        </select>
    </form>
    
    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="nav-link">→</a>
</div>

<?php 
// Вызов функции
generateCalendar($month, $year); 
?>
    
</body>
</html>