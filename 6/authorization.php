<?php

session_start();

if (!empty($_GET['back']) or !empty($_SESSION['admin_id'])) {
    $page_name = 'authorization.php';
    $sign_out_path = getcwd() . '/sign_out.php';
    $_GET['back'] = '1';
    require $sign_out_path;
}

if (!empty($_COOKIE['rerouteButton']))
    setcookie('rerouteButton', '', time() - 3600);

if (!empty($_SERVER['PHP_AUTH_USER']) or !empty($_SERVER['PHP_AUTH_PW'])) {
    if (file_exists(getcwd() . '/log_out.php')) {
        header('Location: ' . substr(
                $_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/')
            ) . '/log_out.php?page_name=authorization.php');
    } else {
        echo '<h1 style="width: fit-content; margin: 50px auto">Произошла ошибка маршрутизации</h1>' ;
        exit();
    }
}

$task_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$task_url = substr($task_url, 0, strripos($task_url, '/'));

$message = (!empty($_COOKIE['sys_messages']['authorization']) and empty($_GET['back'])) ?
    $_COOKIE['sys_messages']['authorization'] : '';
$login_value = (!empty($_COOKIE[session_name()]) and !empty($_SESSION['login']) and empty($_GET['back'])) ?
    $_SESSION['login'] : '';

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
        '/images/icon.png' ?>" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous"
    />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
        '/main_style.css' ?>" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
        '/authorization_style.css' ?>" />
    <title>Авторизация</title>
</head>

<body>
<header class="header">
    <img class="logo" src="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
        '/images/logo.png' ?>" alt="Логотип" />
    <h1 class="title">Задание 6</h1>
</header>

<div class="content">
    <div class="authentication-form">
        <form id="form" action="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
            '/index.php' ?>" method="post">
            <div class="login-block">
                <label>
                    <input type="text"
                           name="login"
                           placeholder="Логин"
                           size="30"
                           value="<?php echo htmlspecialchars($login_value, ENT_QUOTES, 'UTF-8') ?>"
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
                echo '<div class="system-message-block"><p><i>' .
                    htmlspecialchars($message, ENT_QUOTES, 'UTF-8') .
                    '</i></p></div>';

            ?>

            <div class="send-block">
                <button type="submit" name="rerouteButton" value="sign_in">Войти</button>
            </div>
        </form>

        <div class="back-button">
            <a href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
                '/welcome.php?back=1' ?>">
                <img src="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
                    '/images/back.svg' ?>"  alt="..." />
                <b>Перейти на главную страницу</b>
            </a>
        </div>
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
