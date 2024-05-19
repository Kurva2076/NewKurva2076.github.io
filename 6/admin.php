<?php

session_start();

$db_connections_path = getcwd() . "/db_connections.php";
require_once $db_connections_path;

$admin_id = '';
if (!empty($_SERVER['PHP_AUTH_USER']) and !empty($_SERVER['PHP_AUTH_PW'])) {
    $admin_id = DBConnect('AdminDataVerification', login: $_SERVER['PHP_AUTH_USER'], pass: $_SERVER['PHP_AUTH_PW']);
    $_SESSION['admin_id'] = $admin_id;
}

if (empty($admin_id)) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');

    print('<h1 style="width: fit-content; margin: 50px auto">401 Требуется авторизация</h1>');
    exit();
} elseif (!empty($_SESSION['user_id'])) {
    $page_name = 'admin.php';
    $sign_out_path = getcwd() . "/sign_out.php";
    require_once $sign_out_path;
}

$task_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$task_url = substr($task_url, 0, strripos($task_url, '/'));

if (!empty($_GET['delete'])) {
    DBConnect('DeleteUserInfo');
}
$statistic = DBConnect('GetProgramLangsStatistic');
$users_cnt = DBConnect('GetUsersCount');

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
    <link rel="stylesheet" href="<?php print $task_url . '/admin_style.css' ?>" />
    <script defer src="<?php print $task_url . '/admin.js' ?>">
    </script>
    <title>Задание 6</title>
</head>

<body>
<header class="header">
    <img class="logo" src="<?php print $task_url . '/images/logo.png' ?>" alt="Логотип" />
    <h1 class="title">Задание 6</h1>
</header>

<div class="content">
    <section class="table-section">
        <div class="program-langs-statistic-table align-content-center">
            <div class="show-table-button">
                <button type="button"><b>Показать статистику популярности языков программирования</b></button>
            </div>

            <div class="wrapper">
                <table class="table table-bordered">
                    <tr>
                        <?php foreach ($statistic as $lang_info): ?>
                            <th class="align-content-center"> <?php print($lang_info['language_name']) ?> </th>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <?php foreach ($statistic as $lang_info): ?>
                            <th class="align-items-center"> <?php print($lang_info['count_users']) ?> </th>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        </div>
    </section>

    <section class="users-section">
        <div class="cnt-users-block">
            <h3>Зарегистрировано пользователей: <b><?php print $users_cnt ?></b></h3>
        </div>

        <div class="appearance-management">
            <div class="cnt-users-buttons">
                <button class="all-users" type="button" style="opacity: 1;"><b>Все</b></button>
                <button type="button"><b>5</b></button>
                <button type="button"><b>10</b></button>
                <button type="button"><b>15</b></button>
                <button type="button"><b>20</b></button>
            </div>

            <div class="pagination"></div>

            <div class="find-user-block">
                <label><input id="FindUserId" type="text" size="12" placeholder="ID пользователя" /></label>
                <button class="find-button" type="button"><b>Найти</b></button>
            </div>
        </div>

        <div class="users-info-block align-content-center">
            <?php

            $users_table_path = getcwd() . '/users_table.php';
            require $users_table_path;

            ?>
        </div>
    </section>

    <section class="log-out-button-section">
        <div class="back-button">
            <a href="<?php print($task_url . '/welcome.php?back=1') ?>">
                <b><?php print ('Выйти из аккаунта') ?></b>
            </a>
        </div>
    </section>
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
