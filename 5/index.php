<?php

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $task_url = substr($_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/'));

    if (!empty($_COOKIE['rerouteButton']) and strcmp($_COOKIE['rerouteButton'], 'welcome_page') != 0) {
        if (strcmp($_COOKIE['rerouteButton'], 'send') == 0 or
            strcmp($_COOKIE['rerouteButton'], 'sign_in') == 0 or
            strcmp($_COOKIE['rerouteButton'], 'Сохранить') == 0) {
            if (file_exists(getcwd() . "/form.php")) {
                setcookie('rerouteButton', '', time() - 3600);
                header('Location: ' . $task_url . '/form.php');
                exit();
            } else
                echo '<script> alert("Невозможно перенаправить вас на страницу с формой."); </script>';
        } elseif (strcmp($_COOKIE['rerouteButton'], 'edit') == 0 or
            strcmp($_COOKIE['rerouteButton'], 'sign_out') == 0) {
            if (file_exists(getcwd() . "/authorization.php")) {
                setcookie('rerouteButton', '', time() - 3600);
                header('Location: ' . $task_url . '/authorization.php');
                exit();
            } else
                echo '<script>' .
                    'alert("Невозможно перенаправить вас на страницу с аутентификацией пользователя."); ' .
                    '</script>';
        } else {
            setcookie('rerouteButton', '', time() - 3600);
            echo '<script> alert("Некорректное значение."); </script>';
        }
    } else {
        setcookie('rerouteButton', '', time() - 3600);
        header('Location: ' . $task_url . '/welcome.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (strcmp($_POST['rerouteButton'], 'edit') == 0 or
        strcmp($_POST['rerouteButton'], 'send') == 0 or
        strcmp($_POST['rerouteButton'], 'sign_out') == 0 or
        strcmp($_POST['rerouteButton'], 'welcome_page') == 0) {
        if (!empty($_COOKIE['sys_messages']))
            foreach ($_COOKIE['sys_messages'] as $name => $message)
                setcookie('sys_messages[' . $name . ']', 0, time() - 3600, '/');

        setcookie('PHPSESSID', '', time() - 3600, '/');
        session_unset();
        session_abort();

        setcookie('rerouteButton', $_POST['rerouteButton']);
    } elseif (strcmp($_POST['rerouteButton'], 'sign_in') == 0) {
        session_start();

        $error_messages = [
            'login' => [
                400 => 'Введите логин.',
                410 => 'Пользователя с таким логином не найдено.'
            ],
            'password' => [
                400 => 'Введите пароль.',
                410 => 'Введён неверный пароль.'
            ]
        ];
        $system_message = '';

        if (empty($_POST['login'])) {
            $system_message = $error_messages['login'][400];
        } else {
            global $my_username, $my_password, $my_dbname, $user_info;

            $authentication_path = getcwd() . "/server_info.php";
            if (file_exists($authentication_path))
                include $authentication_path;

            $host = 'localhost';
            $dsn = 'mysql:host=' . $host . ';dbname=' . $my_dbname . ';charset=utf8';

            try {
                $db = new PDO($dsn, $my_username, $my_password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_PERSISTENT, true);

                $user_info = $db->query(
                    'SELECT * FROM Users_Authentication WHERE user_login="' . $_POST['login'] . '"'
                )->fetch(PDO::FETCH_ASSOC);

                unset($db);
            } catch (PDOException $e) {
                exit();
            }

            if (empty($user_info))
                $system_message = $error_messages['login'][410];
            elseif (empty($_POST['password']))
                $system_message = $error_messages['password'][400];
            elseif (strcmp(hash('sha512', $_POST['password']), $user_info['user_password']) != 0)
                $system_message = $error_messages['password'][410];
            else
                $_SESSION['user_id'] = $user_info['user_id'];
        }

        $_SESSION['login'] = (empty($_POST['login'])) ? '' : $_POST['login'];

        if (empty($system_message)) {
            if (!empty($_COOKIE['sys_messages']))
                foreach ($_COOKIE['sys_messages'] as $name => $message)
                    setcookie('sys_messages[' . $name . ']', 0, time() - 3600, '/');

            setcookie('rerouteButton', $_POST['rerouteButton']);
        } else {
            setcookie('sys_messages[authorization]', $system_message, 0, '/');
            setcookie('rerouteButton', 'edit');
        }
    } elseif (strcmp($_POST['rerouteButton'], 'Сохранить') == 0) {
        session_start();

        global $my_username, $my_password, $my_dbname;

        $authentication_path = getcwd() . "/server_info.php";
        if (file_exists($authentication_path))
            include $authentication_path;

        $host = 'localhost';
        $dsn = 'mysql:host=' . $host . ';dbname=' . $my_dbname . ';charset=utf8';

        $program_ls = [];

        try {
            $db = new PDO($dsn, $my_username, $my_password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_PERSISTENT, true);

            $lang_stmt = $db->query('SELECT * FROM Programming_Languages');
            while ($row = $lang_stmt->fetch(PDO::FETCH_ASSOC)) {
                $program_ls[] = $row['language_name'];
            }

            unset($db);
        } catch (PDOException $e) {
            exit();
        }

        $error_messages = [
            'fullName' => [
                400 => 'Введите ФИО.',
                401 => 'ФИО не может быть длиннее 150 символов.',
                403 => 'ФИО может содержать только латиницу, кириллицу или " ".',
                405 => 'ФИО не может начинаться/оканчиваться " ".',
                411 => 'Недопустимо использование подряд идущего " ".'
            ],
            'phoneNumber' => [
                400 => 'Введите номер телефона.',
                401 => 'Номер телефона не может быть длиннее 30 символов.',
                403 => 'Номер телефона может содержать только цифры и символы "+", " ".',
                405 => 'Номер телефона не может начинаться/оканчиваться " ".',
                406 => 'Вначале необходим символ "+".',
                407 => 'Для корректного хранения необходим " ".',
                411 => 'Недопустимо использование подряд идущего " ".',
                412 => 'Недопустимо использование знака "+" не вначале.'
            ],
            'email' => [
                400 => 'Введите e-mail.',
                401 => 'E-mail должен быть не длиннее 150 символов.',
                4031 => 'До "@" можно использовать только латиницу, цифры, и символы "_", "-", ".".',
                4032 => 'После "@" можно использовать только латиницу, цифры, символ "-" и одну ".".',
                406 => 'E-mail должен быть введён в следующем формате: "_@_._".',
                414 => 'E-mail не может начинаться с символов "_", "-", ".".',
                415 => 'Имя пользователя не может быть пустым.',
                416 => 'Доменное имя не может быть пустым.',
                417 => 'Код страны не может быть пустым.'
            ],
            'birth' => [
                400 => 'Введите дату рождения.',
                403 => 'Дата рождения может содержать только цифры и символ ".".',
                406 => 'Дата рождения должна быть введена в следующем формате: "дд.мм.гггг".',
                408 => 'Указанная дата должна существовать.',
                409 => 'Указанная дата не может превышать дату текущего дня.',
                413 => 'Вы должны быть старше 14 и моложе 120 лет.'
            ],
            'sex' => [
                400 => 'Укажите ваш пол.',
                410 => 'Введено некорректное значение пола. Не меняйте значения, пожалуйста :(.'
            ],
            'programLanguages' => [
                400 => 'Выберите хотя бы один язык программирования.',
                410 => 'Введён несуществующий в списке язык программирования. Не меняйте значения, пожалуйста :(.'
            ],
            'bio' => [
                400 => 'Введите вашу биографию.',
                402 => 'Размер биографии превышает допустимый размер.'
            ],
            'agreement' => [
                400 => 'Необходимо быть ознакомленным(ой) с контрактом.',
                410 => 'Введёно некорректное значение. Не меняйте значения, пожалуйста :(.'
            ]
        ];
        $system_messages = [
            'fullName' => '',
            'phoneNumber' => '',
            'email' => '',
            'birth' => '',
            'sex' => '',
            'programLanguages' => '',
            'bio' => '',
            'agreement' => ''
        ];

        foreach ($error_messages as $name => $messages) {
            if (empty($_POST[$name])) {
                $system_messages[$name] = $messages[400];
            } else {
                if (strcmp($name, 'fullName') == 0) {
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
                    elseif (str_starts_with($_POST[$name], "@"))
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

            $POST_value = null;
            if (strcmp($name, 'programLanguages') == 0) {
                $POST_value = array();
                foreach ($_POST[$name] as $num => $lang)
                    $POST_value[] = $lang;
                $POST_value = json_encode($POST_value);
            } elseif (strcmp($name, 'birth') == 0) {
                $date = array_map('intval', explode('.', $_POST['birth']));
                $POST_value = (($date[0] > 9) ? $date[0] : ('0' . $date[0])) . '.' .
                    (($date[1] > 9) ? $date[1] : ('0' . $date[1])) . '.' . $date[2];
            } else {
                $POST_value = (strcmp($name, 'agreement') != 0) ? $_POST[$name] :
                    ((array_key_exists('agreement', $_POST)) ? $_POST[$name] : 'off');
            }

            setcookie('sys_messages[' . $name . ']', $system_messages[$name], 0, '/');
            if (empty($_SESSION['user_id']))
                setcookie('values[' . $name . ']', $POST_value, 0, '/');
            else
                $_SESSION['values'][$name] = $POST_value;
        }

        setcookie('rerouteButton', $_POST['rerouteButton']);

        foreach ($system_messages as $name => $message)
            if (strcmp($message, '') != 0) {
                header('Location: index.php');
                exit();
            }

        foreach ($_POST as $name => $value)
            if (strcmp($name, 'rerouteButton') != 0) {
                setcookie('sys_messages[' . $name . ']', '', time() - 3600, '/');

                if (empty($_SESSION['user_id'])) {
                    $POST_value = null;
                    if (strcmp($name, 'programLanguages') == 0) {
                        $POST_value = array();
                        foreach ($value as $num => $lang)
                            $POST_value[] = $lang;
                        $POST_value = json_encode($POST_value);
                    } elseif (strcmp($name, 'birth') == 0) {
                        $date = array_map('intval', explode('.', $_POST['birth']));
                        $POST_value = (($date[0] > 9) ? $date[0] : ('0' . $date[0])) . '.' .
                            (($date[1] > 9) ? $date[1] : ('0' . $date[1])) . '.' . $date[2];
                    } else {
                        $POST_value = (strcmp($name, 'agreement') != 0) ? $value :
                            ((array_key_exists('agreement', $_POST)) ? $value : 'off');
                    }

                    $cur_year = array_map('intval', explode('/', date('m/d/Y')))[2];
                    setcookie(
                        'values[' . $name . ']', $POST_value,
                        time() + 60 * 60 * 24 * (($cur_year % 4 == 0) ? 366 : 365), '/');
                }
            }

        if (empty($_SESSION['user_id'])) {
            $spase_pos = strpos($_POST['fullName'], ' ');
            $name_login = substr($_POST['fullName'], 0, $spase_pos ? $spase_pos : null);
            $login = rand() % 1000000 . substr($name_login, 0, (strlen($name_login) > 52) ? 52 : null) .
                substr($_POST['birth'], strlen($_POST['birth']) - 2);

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
            $pass .= rand() % 1000000;

            try {
                $db = new PDO($dsn, $my_username, $my_password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_PERSISTENT, true);

                $datebirth = explode('.', $_POST['birth']);
                $datebirth = $datebirth[2] . '-' . $datebirth[1] . '-' . $datebirth[0];
                $agreement = (strcmp($_POST['agreement'], 'on') == 0) ? 1 : 0;

                $users_stmt = $db->prepare(
                    "INSERT INTO Users (full_name, birth, sex, phone_number, e_mail, bio, agreement) " .
                    "VALUES (:full_name, :birth, :sex, :phone_number, :e_mail, :bio, :agreement)"
                );
                $users_stmt->bindParam(':full_name', $_POST['fullName']);
                $users_stmt->bindParam(':birth', $datebirth);
                $users_stmt->bindParam(':sex', $_POST['sex']);
                $users_stmt->bindParam(':phone_number', $_POST['phoneNumber']);
                $users_stmt->bindParam(':e_mail', $_POST['email']);
                $users_stmt->bindParam(':bio', $_POST['bio']);
                $users_stmt->bindParam(':agreement', $agreement);
                $users_stmt->execute();

                $user_stmt = $db->query("SELECT user_id FROM Users WHERE user_id = LAST_INSERT_ID()");
                $user_id = $user_stmt->fetch(PDO::FETCH_ASSOC)['user_id'];

                $lang = [];
                foreach ($_POST['programLanguages'] as $language)
                    $lang[$language] = 0;

                $lang_stmt = $db->query('SELECT * FROM Programming_Languages');
                while ($row = $lang_stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($lang[$row['language_name']])) {
                        $lang[$row['language_name']] = $row['language_id'];
                    }
                }

                $users_program_lang_stmt = $db->prepare(
                    "INSERT INTO Users_Programming_Languages (user_id,language_id) " .
                    "VALUES (:user_id,:language_id)"
                );
                foreach ($lang as $language_name => $language_id) {
                    $users_program_lang_stmt->bindParam(':user_id', $user_id);
                    $users_program_lang_stmt->bindParam(':language_id', $language_id);
                    $users_program_lang_stmt->execute();
                }

                $hash_pass = hash('sha512', $pass);
                $users_auth_stmt = $db->prepare(
                    "INSERT INTO Users_Authentication (user_id,user_login,user_password) " .
                    "VALUES (:user_id,:user_login,:user_password)"
                );
                $users_auth_stmt->bindParam(':user_id', $user_id);
                $users_auth_stmt->bindParam(':user_login', $login);
                $users_auth_stmt->bindParam(':user_password', $hash_pass);
                $users_auth_stmt->execute();

                unset($db);
            } catch (PDOException $e) {
                $error_message = 'Ошибка подключения к серверу: ' . $e->getMessage();
                echo "<script> alert(`$error_message`); </script>";
                exit();
            }

            $_SESSION['login'] = $login;
            $_SESSION['password'] = $pass;
        } elseif (!empty($_SESSION['login'])) {
            try {
                $db = new PDO($dsn, $my_username, $my_password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_PERSISTENT, true);

                $datebirth = explode('.', $_POST['birth']);
                $datebirth = $datebirth[2] . '-' . $datebirth[1] . '-' . $datebirth[0];
                $agreement = (strcmp($_POST['agreement'], 'on') == 0) ? 1 : 0;

                $users_stmt = $db->prepare(
                    "UPDATE Users SET full_name = :full_name, birth = :birth, sex = :sex, " .
                    "phone_number = :phone_number, e_mail = :e_mail, bio = :bio, " .
                    "agreement = :agreement WHERE user_id = " . $_SESSION['user_id']
                );
                $users_stmt->bindParam(':full_name', $_POST['fullName']);
                $users_stmt->bindParam(':birth', $datebirth);
                $users_stmt->bindParam(':sex', $_POST['sex']);
                $users_stmt->bindParam(':phone_number', $_POST['phoneNumber']);
                $users_stmt->bindParam(':e_mail', $_POST['email']);
                $users_stmt->bindParam(':bio', $_POST['bio']);
                $users_stmt->bindParam(':agreement', $agreement);
                $users_stmt->execute();

                $db->exec("DELETE FROM Users_Programming_Languages WHERE user_id = " . $_SESSION['user_id']);

                $lang = [];
                foreach ($_POST['programLanguages'] as $language)
                    $lang[$language] = 0;

                $lang_stmt = $db->query('SELECT * FROM Programming_Languages');
                while ($row = $lang_stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($lang[$row['language_name']])) {
                        $lang[$row['language_name']] = $row['language_id'];
                    }
                }

                $users_program_lang_stmt = $db->prepare(
                    "INSERT INTO Users_Programming_Languages (user_id,language_id) " .
                    "VALUES (:user_id,:language_id)"
                );
                foreach ($lang as $language_name => $language_id) {
                    $users_program_lang_stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $users_program_lang_stmt->bindParam(':language_id', $language_id);
                    $users_program_lang_stmt->execute();
                }

                unset($db);
            } catch (PDOException $e) {
                $error_message = 'Ошибка подключения к серверу: ' . $e->getMessage();
                echo "<script> alert(`$error_message`); </script>";
                exit();
            }
        }

        setcookie('save', true, 0, '/');
    }

    header('Location: index.php');
}