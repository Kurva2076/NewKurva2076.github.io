<?php

if (str_contains($_SERVER['PHP_SELF'], 'welcome.php') or
    str_contains($_SERVER['PHP_SELF'], 'admin.php') or
    array_key_exists('back', $_GET) && $_GET['back'] === '1') {
    if (!empty($_COOKIE['sys_messages']))
        foreach ($_COOKIE['sys_messages'] as $name => $message)
            setcookie('sys_messages[' . $name . ']', 0, time() - 3600, '/');

    setcookie(session_name(), '', time() - 3600, '/');
    session_unset();
    session_abort();

    global $page_name;
    $task_url = substr($_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/'));
    header('Location: ' . $task_url . '/' . $page_name);
} else {
    echo '<script> alert("Ошибка в URL-адресе.") </script>';
    exit();
}