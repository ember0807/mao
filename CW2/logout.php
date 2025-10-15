<?php
    session_start(); // 1. Начинаем сессию

    // 2. Удаляем все данные сессии
    $_SESSION = array();
    
    // 3. Если используется сессионная cookie, удаляем её
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 4. Уничтожаем саму сессию на сервере
    session_destroy();
    
    // 5. Перенаправляем пользователя на главную страницу или страницу входа
    header("Location: index.php"); 
    exit;
?>