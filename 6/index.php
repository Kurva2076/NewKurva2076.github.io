<?php

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $task_url = substr($_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/'));

    if (!empty($_COOKIE['rerouteButton'])) {
        $page_name = (strcmp($_COOKIE['rerouteButton'], 'sign_in') == 0 or
            strcmp($_COOKIE['rerouteButton'], 'Сохранить') == 0) ? 'form.php' : (
            (strcmp($_COOKIE['rerouteButton'], 'edit') == 0) ? 'authorization.php' : ''
        );

        if (!empty($page_name) and file_exists(getcwd() . '/' . $page_name)) {
            header('Location: ' . $task_url . '/' . $page_name);
        } elseif (empty($page_name)) {
            echo '<script> alert("Некорректное значение."); </script>';
        } else {
            echo '<script> alert("Невозможно перенаправить вас на нужную страницу."); </script>';
        }
        exit();
    } else {
        setcookie('rerouteButton', '', time() - 3600);
        header('Location: ' . $task_url . '/welcome.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (strcmp($_POST['rerouteButton'], 'sign_in') == 0) {
        session_start();

        global $error_messages;
        $purpose = 'authorization';
        $error_messages_path = getcwd() . "/error_messages.php";
        require $error_messages_path;

        $system_message = GetAuthSystemMessage($error_messages);
        $_SESSION['login'] = (empty($_POST['login'])) ? '' : $_POST['login'];

        if (empty($system_message)) {
            DeleteCookiesSysMessages();
            setcookie('rerouteButton', $_POST['rerouteButton']);
        } else {
            setcookie('sys_messages[authorization]', $system_message, 0, '/');
            setcookie('rerouteButton', 'edit');
        }
    } elseif (strcmp($_POST['rerouteButton'], 'Сохранить') == 0) {
        session_start();

        UseDB();
        $program_ls = DBConnect('GetProgramLanguages');
        if (empty($program_ls)) {
            exit();
        }

        global $error_messages;
        $purpose = 'form';
        $error_messages_path = getcwd() . "/error_messages.php";
        require $error_messages_path;

        $system_messages = GetFormSystemMessages($error_messages, (array) $program_ls);

        setcookie('rerouteButton', $_POST['rerouteButton']);

        foreach ($system_messages as $name => $message)
            if (!empty($message)) {
                header('Location: index.php');
                exit();
            }

        foreach ($_POST as $name => $value)
            if (strcmp($name, 'rerouteButton') != 0) {
                setcookie('sys_messages[' . $name . ']', '', time() - 3600, '/');

                if (empty($_SESSION['user_id'])) {
                    $POST_value = GetPostValue($name, $value);
                    $cur_year = array_map('intval', explode('/', date('m/d/Y')))[2];

                    setcookie(
                        'values[' . $name . ']', $POST_value,
                        time() + 60 * 60 * 24 * (($cur_year % 4 == 0) ? 366 : 365), '/');
                }
            }

        if (empty($_SESSION['user_id'])) {
            $log = GenerateLogin();
            $pass = GeneratePassword();

            if (!DBConnect('InsertUpdateUserInfo Insert', $login = $log, $password = $pass)) {
                exit();
            }

            $_SESSION['login'] = $login;
            $_SESSION['password'] = $pass;
        } elseif (!empty($_SESSION['login']) or !empty($_SESSION['admin_id'])) {
            if (!DBConnect('InsertUpdateUserInfo Update')) {
                exit();
            }
        }

        setcookie('save', true, 0, '/');
    }

    header('Location: index.php');
}

function GetFormSystemMessages (array $error_messages, array $program_ls) : array
{
    $system_messages = [];

    foreach ($error_messages as $name => $messages) {
        if (empty($_POST[$name])) {
            $system_messages[$name] = $messages[400];
        } else {
            if (strcmp($name, 'userID') == 0) {
                if (preg_match("/[^0-9]/ui", $_POST[$name]))
                    $system_messages[$name] = $messages[403];
                elseif (strcmp($_POST[$name], '0') == 0)
                    $system_messages[$name] = $messages[418];
            } elseif (strcmp($name, 'fullName') == 0) {
                if (strlen($_POST[$name]) > 150)
                    $system_messages[$name] = $messages[401];
                elseif (preg_match("/[^a-zа-яё ]/ui", $_POST[$name]))
                    $system_messages[$name] = $messages[403];
                elseif (preg_match("/^\s|\s$/", $_POST[$name]))
                    $system_messages[$name] = $messages[405];
                elseif (preg_match("/\s\s/", $_POST[$name]))
                    $system_messages[$name] = $messages[411];
            } elseif (strcmp($name, 'phoneNumber') == 0) {
                if (strlen($_POST[$name]) > 30)
                    $system_messages[$name] = $messages[401];
                elseif (preg_match("/[^0-9+ ]/", $_POST[$name]))
                    $system_messages[$name] = $messages[403];
                elseif (preg_match("/^\s|\s$/", $_POST[$name]))
                    $system_messages[$name] = $messages[405];
                elseif (!preg_match("/^\+/", $_POST[$name]))
                    $system_messages[$name] = $messages[406];
                elseif (!preg_match("/\s/", $_POST[$name]))
                    $system_messages[$name] = $messages[407];
                elseif (preg_match("/\s\s/", $_POST[$name]))
                    $system_messages[$name] = $messages[411];
                elseif (preg_match("/(?<!^)\+/", $_POST[$name]))
                    $system_messages[$name] = $messages[412];
            } elseif (strcmp($name, 'email') == 0) {
                if (strlen($_POST[$name]) > 150)
                    $system_messages[$name] = $messages[401];
                elseif (!preg_match("/^.*@.*\..*$/", $_POST[$name]))
                    $system_messages[$name] = $messages[406];
                elseif (!preg_match("/^[a-z0-9_\-.]*@/i", $_POST[$name]))
                    $system_messages[$name] = $messages[4031];
                elseif (!preg_match("/@[a-z0-9\-]*\.[a-z0-9\-]*$/i", $_POST[$name]))
                    $system_messages[$name] = $messages[4032];
                elseif (preg_match("/^[_\-.]/", $_POST[$name]))
                    $system_messages[$name] = $messages[414];
                elseif (preg_match("/^@/", $_POST[$name]))
                    $system_messages[$name] = $messages[415];
                elseif (preg_match("/@\./", $_POST[$name]))
                    $system_messages[$name] = $messages[416];
                elseif (preg_match("/\.$/", $_POST[$name]))
                    $system_messages[$name] = $messages[417];
            } elseif (strcmp($name, 'birth') == 0) {
                if (preg_match("/[^0-9.]/", $_POST[$name]))
                    $system_messages[$name] = $messages[403];
                elseif (!preg_match("/^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]+$/", $_POST[$name]))
                    $system_messages[$name] = $messages[406];
                else {
                    $date = array_map('intval', explode('.', $_POST['birth']));

                    if ($date[0] < 1 or $date[0] > 31 or $date[1] < 1 or $date[1] > 12 or $date[2] < 1
                        or ($date[2] % 4 != 0 and $date[1] == 2 and $date[0] > 28)
                        or ($date[2] % 4 == 0 and $date[1] == 2 and $date[0] > 29)
                        or ($date[1] > 1 and $date[1] < 7 and $date[1] % 2 == 0 and $date[0] > 30)
                        or ($date[1] > 7 and $date[1] < 12 and $date[1] % 2 == 1 and $date[0] > 30)) {
                        $system_messages[$name] = $messages[408];
                    } else {
                        $cur_date = array_map('intval', explode('/', date('d/m/Y')));

                        if ($date[2] > $cur_date[2] or
                            $date[2] == $cur_date[2] and $date[1] > $cur_date[1] or
                            $date[2] == $cur_date[2] and $date[1] == $cur_date[1] and $date[0] > $cur_date[0]) {
                            $system_messages[$name] = $messages[409];
                        } elseif ($date[2] > $cur_date[2] - 14 or $date[2] < $cur_date[2] - 120) {
                            $system_messages[$name] = $messages[413];
                        }
                    }
                }
            } elseif (strcmp($name, 'sex') == 0) {
                if (!preg_match("/^[MF]$/", $_POST[$name]))
                    $system_messages[$name] = $messages[410];
            } elseif (strcmp($name, 'programLanguages') == 0) {
                $is_programLanguages_correctly = true;

                foreach ($_POST[$name] as $language)
                    if (!in_array($language, $program_ls, true))
                        $is_programLanguages_correctly = false;

                if (!$is_programLanguages_correctly)
                    $system_messages[$name] = $messages[410];
            } elseif (strcmp($name, 'bio') == 0) {
                if (strlen($_POST[$name]) > 66560)
                    $system_messages[$name] = $messages[402];
            } elseif (strcmp($name, 'agreement') == 0) {
                if (strcmp($_POST[$name], 'on'))
                    $system_messages[$name] = $messages[410];
            }
        }

        if (empty($system_messages[$name]))
            $system_messages[$name] = '';

        $POST_value = GetPostValue($name, $_POST[$name]);

        setcookie('sys_messages[' . $name . ']', $system_messages[$name], 0, '/');
        if (empty($_SESSION['user_id']))
            setcookie('values[' . $name . ']', $POST_value, 0, '/');
        else
            $_SESSION['values'][$name] = $POST_value;
    }

    return $system_messages;
}

function GetAuthSystemMessage ($error_messages) : string
{
    if (empty($_POST['login'])) {
        return $error_messages['login'][400];
    }

    UseDB();
    $user_auth_info = DBConnect('GetUserAuthInfo');

    if (empty($user_auth_info)) {
        return $error_messages['login'][410];
    } elseif (empty($_POST['password'])) {
        return $error_messages['password'][400];
    } elseif (strcmp(hash('sha512', $_POST['password']), $user_auth_info['user_password']) != 0) {
        return $error_messages['password'][410];
    } else {
        $_SESSION['user_id'] = $user_auth_info['user_id'];
        return '';
    }
}

function GetPostValue (string $name, string | array | null $value) : string | array | bool | null
{
    if (strcmp($name, 'programLanguages') == 0) {
        $POST_value = array();
        foreach ($value as $lang)
            $POST_value[] = $lang;
        return json_encode($POST_value);
    } else {
        return (strcmp($name, 'agreement') != 0) ? $value :
            ((array_key_exists('agreement', $_POST)) ? $value : 'off');
    }
}

function GenerateLogin () : string
{
    $name_login = substr($_POST['fullName'], 0, strpos($_POST['fullName'], ' ') ?: null);
    return rand() % 1000000 . substr($name_login, 0, (strlen($name_login) > 52) ? 52 : null) .
        substr($_POST['birth'], strlen($_POST['birth']) - 2);
}

function GeneratePassword () : string
{
    $pass = substr($_POST['birth'], 0, 2);
    $pass .= rand() % 1000000;
    for ($i = 0; $i < sizeof($_POST['programLanguages']); $i++) {
        $lang_pass = '';
        for ($pos = strlen($_POST['programLanguages'][$i]) - 1; $pos > -1; $pos -= 2)
            $lang_pass .= $_POST['programLanguages'][$i][$pos];
        $pass .= $lang_pass;
    }
    $pass .= substr($_POST['birth'], 3, 2);
    for ($i = 0; $i < strlen($_POST['email']); $i += 3) {
        $pass .= $_POST['email'][$i];
    }
    $pass .= substr($_POST['birth'], strlen($_POST['birth']) - 3);
    for ($i = 0; $i < strlen($_POST['fullName']); $i += 4) {
        $pass .= ord($_POST['fullName'][$i]);
    }
    return $pass . rand() % 1000000;
}

function UseDB (): void
{
    $db_connections_path = getcwd() . "/db_connections.php";
    require_once $db_connections_path;
}

function DeleteCookiesSysMessages () : void
{
    if (!empty($_COOKIE['sys_messages']))
        foreach ($_COOKIE['sys_messages'] as $name => $message)
            setcookie('sys_messages[' . $name . ']', 0, time() - 3600, '/');
}