<?php

if (!empty($_COOKIE[session_name()])) {
    session_start();
}

if (!empty($_COOKIE['rerouteButton'])) {
    setcookie('rerouteButton', '', time() - 3600);
}

if ((!empty($_SERVER['PHP_AUTH_USER']) or !empty($_SERVER['PHP_AUTH_PW'])) and empty($_SESSION['admin_id']) or
    !empty($_SESSION['admin_id']) and empty($_SESSION['user_id']) and empty($_GET['admins_edit'])) {
    if (file_exists(getcwd() . '/log_out.php')) {
        if (!empty($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
            session_unset();
            session_abort();
        }

        header('Location: ' . substr(
                $_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/')
            ) . '/log_out.php?page_name=form.php');
    } else {
        echo '<h1 style="width: fit-content; margin: 50px auto">Произошла ошибка маршрутизации</h1>';
    }
    exit();
} elseif (!empty($_SESSION['admin_id']) and empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = (int) $_GET['admins_edit'];
}

$db_connections_path = getcwd() . "/db_connections.php";
require_once $db_connections_path;

$program_ls = DBConnect('GetProgramLanguages');
if (empty($program_ls)) {
    exit();
}

$task_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$task_url = substr($task_url, 0, strripos($task_url, '/'));

$messages = [];
$errors = [];
$values = [
    'userID' => '',
    'fullName' => '',
    'phoneNumber' => '',
    'email' => '',
    'birth' => '',
    'sex' => '',
    'programLanguages' => [],
    'bio' => '',
    'agreement' => ''
];

if (empty($_SESSION['admin_id'])) {
    unset($values['userID']);
}

if (!empty($_SESSION['user_id'])) {
    if (empty($_COOKIE['sys_messages']) and empty($_COOKIE['save'])) {
        $user_info = DBConnect('GetUserInfo', user_id: $_SESSION['user_id']);
        if (empty($user_info)) {
            echo "<script> alert('Произошла неизвестная ошибка при авторизации.'); </script>";
            exit();
        }

        foreach ($values as $name => $value)
            foreach ($user_info as $col_name => $bd_value)
                if (strcasecmp($name, str_replace('_', '', $col_name)) == 0) {
                    $values[$name] = ($name !== 'agreement') ? $bd_value : (
                            ($bd_value == 1) ? 'on' : 'off'
                    );
                }
    } else {
        foreach ($_SESSION['values'] as $name => $value)
            $values[$name] = ($name === 'programLanguages') ? json_decode($value) : $value;

        if (!empty($_COOKIE['save']))
            unset($_SESSION['values']);
    }
} elseif (!empty($_COOKIE['values'])) {
    foreach ($_COOKIE['values'] as $name => $value)
        $values[$name] = ($name === 'programLanguages') ? json_decode($value) : $value;
}

if (!empty($_COOKIE['save'])) {
    setcookie('save', '', time() - 3600, '/');
    $messages[] = "Всё успешно сохранено.";
}

if (!empty($_COOKIE['sys_messages'])) {
    foreach ($_COOKIE['sys_messages'] as $name => $error_message) {
        if (array_key_exists($name, $values)) {
            $errors[] = $name;
            $messages[$name] = $error_message;
        }
    }
}

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
        '/form_style.css' ?>" />
    <title>Форма</title>
</head>

<body>
<header class="header">
    <img class="logo" src="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
        '/images/logo.png' ?>" alt="Логотип" />
    <h1 class="title">Задание 6</h1>
</header>

<div class="content">
    <div id="application-form" <?php if (!empty($errors)) echo "style='grid-template-columns: 1fr 1fr'"; ?> >
        <form id="form" action="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
            '/index.php' ?>" method="post">
            <div class="form-block-element system-message">
                <?php

                if (empty($_SESSION['admin_id'])) {
                    if (!empty($_SESSION['user_id'])) {
                        echo '<p style="color: #221257">Вход с логином ' .
                            htmlspecialchars($_SESSION['login'], ENT_QUOTES, 'UTF-8') . ', id ' .
                            htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '</p>';
                    } elseif (!empty($_SESSION['password']) and !empty($messages[0])) {
                        echo '<p style="color: #221257">Для редактирования формы воспользуйтесь логином - ' .
                            htmlspecialchars($_SESSION['login'], ENT_QUOTES, 'UTF-8') . ', паролем - ' .
                            htmlspecialchars($_SESSION['password'], ENT_QUOTES, 'UTF-8') . '</p>';

                        setcookie(session_name(), '', time() - 3600, '/');
                        session_unset();
                        session_abort();
                    }
                }

                ?>
            </div>

            <?php if (!empty($_SESSION['admin_id'])): ?>

                <div class="form-block-element">
                    <label class="form-text label" for="userID">
                        ID пользователя
                    </label>

                    <div class="form-element">
                        <input <?php if (in_array('userID', $errors, true)) echo 'class="error"' ?>
                                id="userID"
                                name="userID"
                                type="text"
                                placeholder="Введите ID пользователя"
                                size="30"
                                value="<?php echo htmlspecialchars(
                                        $values['userID'], ENT_QUOTES, 'UTF-8'
                                ); ?>"
                        />
                    </div>
                </div>

            <?php endif; ?>

            <div class="form-block-element">
                <label class="form-text label" for="fullName">
                    ФИО
                </label>

                <div class="form-element">
                    <input <?php if (in_array('fullName', $errors, true)) echo 'class="error"' ?>
                           id="fullName"
                           name="fullName"
                           type="text"
                           placeholder="Введите ФИО"
                           size="30"
                           value="<?php echo htmlspecialchars(
                               $values['fullName'], ENT_QUOTES, 'UTF-8'
                           ); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="phoneNumber">
                    Телефон
                </label>

                <div class="form-element">
                    <input <?php if (in_array('phoneNumber', $errors, true)) echo 'class="error"' ?>
                           id="phoneNumber"
                           name="phoneNumber"
                           type="tel"
                           placeholder="Введите номер телефона"
                           size="30"
                           value="<?php echo htmlspecialchars(
                               $values['phoneNumber'], ENT_QUOTES, 'UTF-8'
                           ); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="email">
                    E-mail
                </label>

                <div class="form-element">
                    <input <?php if (in_array('email', $errors, true)) echo 'class="error"' ?>
                           id="email"
                           name="email"
                           type="text"
                           placeholder="Введите e-mail"
                           size="30"
                           value="<?php echo htmlspecialchars(
                               $values['email'], ENT_QUOTES, 'UTF-8'
                           ); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="birth">
                    Дата рождения
                </label>

                <div class="form-element">
                    <input <?php if (in_array('birth', $errors, true)) echo 'class="error"' ?>
                           id="birth"
                           name="birth"
                           type="text"
                           size="10"
                           placeholder="дд.мм.гггг"
                           value="<?php echo htmlspecialchars(
                               $values['birth'], ENT_QUOTES, 'UTF-8'
                           ); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <div class="form-text label">Укажите ваш пол</div>

                <div class="form-element">
                    <div>
                        <input <?php if (in_array('sex', $errors, true)) echo 'class="error"' ?>
                                id="male"
                                type="radio"
                                name="sex"
                                value="M"
                                <?php

                                if ($values['sex'] === 'M')
                                    echo 'checked';

                                ?>
                        />
                        <label class="form-text" for="male">М</label>
                        <input <?php if (in_array('sex', $errors, true)) echo 'class="error"' ?>
                                id="female"
                                type="radio"
                                name="sex"
                                value="F"
                                <?php

                                if ($values['sex'] === 'F')
                                    echo 'checked';

                                ?>
                        />
                        <label class="form-text" for="female">Ж</label>
                    </div>
                </div>
            </div>

            <div class="form-block-element" style="margin-top: 13px">
                <label class="form-text label" for="programLanguages">
                    Укажите любимые <br /> языки <br /> программирования
                </label>

                <div class="form-element">
                    <select <?php if (in_array('programLanguages', $errors, true)) echo 'class="error"' ?>
                            id="programLanguages"
                            name="programLanguages[]"
                            size="5"
                            multiple="multiple"
                    >
                        <?php

                        foreach ($program_ls as $program_l) {
                            echo '<option value="' . htmlspecialchars($program_l, ENT_QUOTES, 'UTF-8') .
                                '"';
                            if (in_array($program_l, $values['programLanguages'])) {
                                echo ' selected';
                            }
                            echo '>';

                            echo htmlspecialchars($program_l, ENT_QUOTES, 'UTF-8');

                            echo '</option>';
                        }

                        ?>
                    </select>
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="bio">
                    Биография
                </label>

                <div class="form-element">
                    <textarea class="textarea <?php if (in_array('bio', $errors, true)) echo 'error' ?>"
                              id="bio"
                              name="bio"
                              placeholder="Введите биографию"
                    ><?php echo htmlspecialchars($values['bio'], ENT_COMPAT, 'UTF-8') ?></textarea>
                </div>
            </div>

            <?php if (empty($_SESSION['admin_id'])): ?>
                <div class="form-block-element">
                    <div class="checkFormElement">
                        <label>
                            <input <?php if (in_array('agreement', $errors, true)) echo 'class="error"' ?>
                                    type="checkbox"
                                    name="agreement"
                                    <?php

                                    if ($values['agreement'] === 'on')
                                        echo 'checked';

                                    ?>
                            />
                            <b class="form-text agreement">С контрактом ознакомлен(а)</b>
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-block-element send-button">
                <p><input class="rerouteButton" type="submit" name="rerouteButton" value="Сохранить"></p>
                <?php

                if (empty($errors) and !empty($messages[0]))
                    echo '<p style="color: #221257; padding: 14px 0 0 25px;">' .
                        htmlspecialchars($messages[0], ENT_QUOTES, 'UTF-8') . '</p>';

                ?>
            </div>
        </form>

        <?php

        if (!empty($errors)) {
            $height = empty($_SESSION['user_id']) ? 540 : (!empty($_SESSION['admin_id']) ? 580 : 500);
            echo '<div class="messages" style="height: ' . $height . 'px;">';

            foreach ($values as $name => $value) {
                $height = $name === 'bio' ? 97 : ($name === 'programLanguages' ? 102 : '');
                $error_message = '';
                if (array_key_exists($name, $messages))
                    $error_message = $messages[$name];

                echo '<div class="message ' . $name . '" style="height: ' .
                    htmlspecialchars($height, ENT_QUOTES, 'UTF-8') . 'px;' .
                    (!empty($error_message) ? '"' : ' visibility: hidden;"') . '>' .
                    htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . '</div>';
            }

            echo '</div>';
        }

        ?>

        <div class="back-button">
            <a href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') . (
                    (!empty($_SESSION['admin_id'])) ? '/admin.php' : (
                    (!empty($_SESSION['user_id'])) ? '/authorization.php' : '/welcome.php'
                    )) . '?back=1' ?>"
            >
                <img src="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8') .
                    '/images/back.svg' ?>"  alt="..." />

                <b>
                    <?php echo (
                    (!empty($_SESSION['admin_id'])) ? 'Вернуться на страницу админа' : (
                    (!empty($_SESSION['user_id'])) ? 'Выйти из аккаунта' : 'Перейти на главную страницу'
                    )) ?>
                </b>
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