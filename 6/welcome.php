<?php

print_r($_SERVER);

$task_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$task_url = substr($task_url, 0, strripos($task_url, '/'));

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
    <link rel="stylesheet" href="<?php print $task_url . '/welcome_style.css' ?>" />
    <title>Задание 6</title>
</head>

<body>
<header class="header">
    <img class="logo" src="<?php print $task_url . '/images/logo.png' ?>" alt="Логотип" />
    <h1 class="title">Задание 6</h1>
</header>

<div class="content">
    <form action="<?php print $task_url . '/index.php' ?>" method="post">
        <div class="action-buttons">
            <div class="send-new-form">
                <button type="submit" name="rerouteButton" value="send">Отправить новую форму</button>
            </div>

            <div class="edit-old-form">
                <button type="submit" name="rerouteButton" value="edit">Изменить данные в старой форме</button>
            </div>

            <div class="admin-block">
                <button type="submit" name="rerouteButton" value="admin">Войти в систему под админом</button>
            </div>
        </div>
    </form>
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