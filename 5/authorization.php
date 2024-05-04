<?php

if (!empty($_COOKIE['PHPSESSID'])) {
    session_start();
}

$task_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$task_url = substr($task_url, 0, strripos($task_url, '/'));

$message = (!empty($_COOKIE['sys_messages']['authorization'])) ? $_COOKIE['sys_messages']['authorization'] : '';
$login_value = (!empty($_SESSION['login'])) ? $_SESSION['login'] : '';

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="<?php print $task_url . '/images/icon.png' ?>" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous"
    />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="<?php print $task_url . '/main_style.css' ?>" />
    <link rel="stylesheet" href="<?php print $task_url . '/authorization_style.css' ?>" />
    <title>Авторизация</title>
</head>

<body>
<header class="header">
    <img class="logo" src="<?php print $task_url . '/images/logo.png' ?>" alt="Логотип" />
    <h1 class="title">Задание 5</h1>
</header>

<div class="content">
    <div class="authentication-form">
        <form id="form" action="<?php print $task_url . '/index.php' ?>" method="post">
            <div class="login-block">
                <label>
                    <input type="text"
                           name="login"
                           placeholder="Логин"
                           size="30"
                           value="<?php print($login_value); ?>"
                    />
                </label>
            </div>

            <div class="password-block">
                <label>
                    <input type="text"
                           name="password"
                           placeholder="Пароль"
                           size="30"
                    />
                </label>
            </div>

            <?php

            if (!empty($message))
                print('<div class="system-message-block"><p><i>' . $message . '</i></p></div>');

            ?>

            <div class="send-block">
                <button type="submit" name="rerouteButton" value="sign_in">Войти</button>
            </div>
        </form>

        <form id="back" action="<?php print $task_url . '/index.php' ?>" method="post">
            <button type="submit" name="rerouteButton" value="welcome_page">Перейти на главную страницу</button>
        </form>
    </div>
</div>

<div class="footer">
    <footer>
        <div class="footer-content">
            (с) Петров Семён, 2024
        </div>
    </footer>
</div>
</body>
</html>
