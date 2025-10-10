<?php
    session_start();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if(!empty($username) && !empty($password)) {
            $userFile = 'users.txt';
            $users = file_exists($userFile) ? file($userFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
            $userExists = false;

            foreach($users as $user) {
                list($existingUserName, $_) = explode(':', $user);
                if($existingUserName === $username) {
                    $userExists = true;
                    break;
                }
            }

            if($userExists) {
                $error = "Имя пользователя занято";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                file_put_contents($userFile, "$username:$passwordHash\n", FILE_APPEND);
                header("Location: login.php");
                exit;
            }
        } else {
            $error = "Заполните все поля";
        }
    }
?>

<html lang="ru">
    <head>
        <meta charset="utf8">
        <title>Register page</title>
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
            }

            h2 {
                font-size: 2rem;
            }

            .container__form {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            input {
                padding: 0.75rem;
                background-color: transparent;
                border: 2px solid #000000;
                border-radius: 0.25rem;

            }

            button {
                background-color: blue;
                color: white;
                border-radius: 0.25rem;
                padding: 0.75rem;
                border: none;
                cursor: pointer;
            }

            button {
                background-color: darkblue;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <h2>Register form</h2>
            <?php if(isset($error)): ?>
                <p class="error"><?php echo $error;?></p>
            <?php endif; ?>
            <form method="POST" class="container__form">                    
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <button type="submit">Sign Up</button>
            </form>
        </div>
    </body>
</html>