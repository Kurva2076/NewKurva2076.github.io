<?php

if (!empty($_SERVER['PHP_AUTH_USER']) or !empty($_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');

    print('<h1 style="width: fit-content; margin: 50px auto">' .
        'Необходимо выйти из аккаунта (отправить пустые поля или нажать отменить)</h1>');
} else {
    if (file_exists(getcwd() . '/' . $_GET['page_name'])) {
        header('Location: ' . substr(
                $_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/')
            ) . '/' . $_GET['page_name']);
    } else {
        print('<h1 style="width: fit-content; margin: 50px auto">Произошла ошибка маршрутизации</h1>');
    }
}

exit();