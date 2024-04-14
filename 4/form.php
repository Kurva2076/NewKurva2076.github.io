<?php

global $my_username, $my_password, $my_dbname;
$authentication_path = getcwd() . "/authentication.php";
if (file_exists($authentication_path))
    include $authentication_path;

$host = 'localhost';
$dsn = 'mysql:host=' . $host . ';dbname=' . $my_dbname . ';charset=utf8';

try {
    $db = new PDO($dsn, $my_username, $my_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_PERSISTENT, true);

    $lang_stmt = $db->query('SELECT * FROM Programming_Languages');

    $rows = $lang_stmt->fetchAll(PDO::FETCH_ASSOC);

    unset($db);
} catch (PDOException $e) {
    $error_message = 'Ошибка подключения к серверу: ' . $e->getMessage();
    echo "<script> alert(`$error_message`); </script>";
    exit();
}

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
    <link rel="stylesheet" href="<?php print $task_url . '/style.css' ?>" />
    <title>Задание 4</title>
</head>

<body>
<header class="header">
    <img class="logo" src="<?php print $task_url . '/images/logo.png' ?>" alt="Логотип" />
    <h1 class="title">Задание 4</h1>
</header>

<div class="content">
    <div id="application-form" <?php
    global $messages, $errors, $values;
    if ($errors[0]) print("style='grid-template-columns: 1fr 1fr'");
    ?>
    >
        <form id="form" action="<?php print $task_url . '/index.php' ?>" method="post">
            <div class="form-block-element">
                <label class="form-text label" for="fullName">
                    ФИО
                </label>

                <div class="form-element">
                    <input <?php if (in_array('fullName', $errors, true)) print('class="error"')?>
                           id="fullName"
                           name="fullName"
                           type="text"
                           placeholder="Введите ФИО"
                           size="30"
                           value="<?php print($values['fullName']); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="phoneNumber">
                    Телефон
                </label>

                <div class="form-element">
                    <input <?php if (in_array('phoneNumber', $errors, true)) print('class="error"')?>
                           id="phoneNumber"
                           name="phoneNumber"
                           type="tel"
                           placeholder="Введите номер телефона"
                           size="30"
                           value="<?php print($values['phoneNumber']); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="email">
                    E-mail
                </label>

                <div class="form-element">
                    <input <?php if (in_array('email', $errors, true)) print('class="error"')?>
                           id="email"
                           name="email"
                           type="text"
                           placeholder="Введите e-mail"
                           size="30"
                           value="<?php print($values['email']); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="birth">
                    Дата рождения
                </label>

                <div class="form-element">
                    <input <?php if (in_array('birth', $errors, true)) print('class="error"')?>
                           id="birth"
                           name="birth"
                           type="text"
                           size="10"
                           placeholder="дд.мм.гггг"
                           value="<?php print($values['birth']); ?>"
                    />
                </div>
            </div>

            <div class="form-block-element">
                <div class="form-text label">Укажите ваш пол</div>

                <div class="form-element">
                    <div>
                        <input <?php if (in_array('sex', $errors, true)) print('class="error"')?>
                                id="male"
                                type="radio"
                                name="sex"
                                value="M"
                                <?php

                                if (strcmp($values['sex'], 'M') == 0)
                                    print('checked');

                                ?>
                        />
                        <label class="form-text" for="male">М</label>
                        <input <?php if (in_array('sex', $errors, true)) print('class="error"')?>
                                id="female"
                                type="radio"
                                name="sex"
                                value="F"
                                <?php

                                if (strcmp($values['sex'], 'F') == 0)
                                    print('checked');

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
                    <select <?php if (in_array('programLanguages', $errors, true)) print('class="error"')?>
                            id="programLanguages"
                            name="programLanguages[]"
                            size="5"
                            multiple="multiple"
                    >
                        <?php foreach ($rows as $row): ?>
                            <option value="<?php echo $row['language_name']; ?>"
                                    <?php

                                    if (in_array($row['language_name'], $values['programLanguages']))
                                        print('selected');

                                    ?>
                            >
                                <?php echo htmlspecialchars($row['language_name']); ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="form-block-element">
                <label class="form-text label" for="bio">
                    Биография
                </label>

                <div class="form-element">
                    <textarea class="textarea <?php if (in_array('bio', $errors, true)) print('error'); ?>"
                              id="bio"
                              name="bio"
                              placeholder=""
                    ><?php print($values['bio']); ?></textarea>
                </div>
            </div>

            <div class="form-block-element">
                <div class="checkFormElement">
                    <label>
                        <input <?php if (in_array('agreement', $errors, true)) print('class="error"')?>
                                type="checkbox"
                                name="agreement"
                                <?php

                                if (strcmp($values['agreement'], 'on') == 0)
                                    print('checked');

                                ?>
                        />
                        <b class="form-text agreement">С контрактом ознакомлен(а)</b>
                    </label>
                </div>
            </div>

            <div class="form-block-element">
                <p><input class="sendButton" type="submit" name="save" value="Сохранить"></p>
                <?php

                if (!$errors[0] && sizeof($messages) == 1)
                    print('<p style="color: #221257">' . $messages[0] . '</p>');

                ?>
            </div>
        </form>

        <?php

        if ($errors[0]) {
            print('<div class="messages">');

            foreach ($values as $name => $value) {
                $style = (strcmp($name, 'bio') == 0) ? 'height: 97px;' :
                    ((strcmp($name, 'programLanguages') == 0) ? 'height: 102px;' : '');
                $error_message = '';
                if (array_key_exists($name, $messages))
                    $error_message = $messages[$name];

                print('<div class="message ' . $name . '" style="' . $style . ((strcmp($error_message, '') != 0) ? '"'
                        : ' visibility: hidden;"') . '>' . htmlspecialchars($error_message) . '</div>');
            }

            print('</div>');
        }

        ?>
    </div>
</div>

<div class="footer">
    <footer>
        <div class="footer-content">
            (c) Петров Семён, 2024
        </div>
    </footer>
</div>
</body>
</html>