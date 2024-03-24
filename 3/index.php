<?php

header('Content-Type: text/html; charset=UTF-8');


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    include("form.php");
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//    setlocale(LC_ALL, "ro_RO.UTF-8");

    $required_fields = [
        'fullName' => [
            'Empty' => 'ФИО',
            'notCorrectly' => 'ФИО не может содержать в себе цифры и (или) специальные символы, ' .
                'должно быть не длинее 150 символов, а также не должно оканчиваться пробелом.'
        ],
        'phoneNumber' => [
            'Empty' => 'номер телефона',
            'notCorrectly' => 'Номер телефона не может содержать в себе ничего кроме цифр и символа "+" вначале, ' .
                'а также необходим не подряд идущий разделитель " ", который не оканчивает номер.'
        ],
        'email' => [
            'Empty' => 'e-mail',
            'notCorrectly' => 'E-mail введён некорректно (_@_._).'
        ],
        'birth' => [
            'Empty' => 'дату рождения',
            'notCorrectly' => 'Дата рождения введена не корректно.',
            'outOfRange' => 'Вы не подходите по возрасту.'
        ],
        'sex' => [
            'Empty' => 'ваш пол',
            'notCorrectly' => 'Введено некорректное значение пола.'
        ],
        'programLanguages' => [
            'Empty' => 'язык программирования',
            'notCorrectly' => 'Введён несуществующий в списке язык программирования'
        ],
        'bio' => [
            'Empty' => 'биографию',
            'notCorrectly' => 'Размер биографии превышает допустимый размер.'
        ],
        'agreement' => [
            'notCorrectly' => 'Необходимо быть ознакомленным (ой) с контрактом.'
        ]
    ];
    $program_ls = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python",
        "Java", "Haskel", "Clojure", "Prolog", "Scala"];
    $system_message = '';

    // Проверка полей на пустоту
    foreach ($required_fields as $name => $message) {
        if (empty($_POST[$name]) and $name != 'agreement') {
            $system_message = ($name == 'sex') ? 'Укажите ' :
                (($name == 'programLanguages') ? 'Выберите ' : 'Введите ');
            $system_message = $system_message . $message['Empty'] . '.';
            $error = true;
            break;
        }
    }

    // Проверка полей на корретность заполнения
    if ($system_message == '') {
        // Проверка содержимого programLanguages
        $is_programLanguages_correctly = true;
        foreach ($_POST['programLanguages'] as $language)
            if (!in_array($language, $program_ls, true))
                $is_programLanguages_correctly = false;

        if (preg_match("/[^a-zа-яё ]|\s$|\s\s/ui", $_POST['fullName']) or
            mb_strlen($_POST['fullName'], "UTF-8") > 150) {
            $system_message = $required_fields['fullName']['notCorrectly'];
        } elseif (preg_match("/[^0-9+ ]|\s$|\s\s|\+{2,}|(?<!^)\+/", $_POST['phoneNumber']) or
            !preg_match("/\s/", $_POST['phoneNumber']) or
            mb_strlen($_POST['phoneNumber'], "UTF-8") > 30) {
            $system_message = $required_fields['phoneNumber']['notCorrectly'];
        } elseif (!preg_match("/^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/", $_POST['email'])) {
            $system_message = $required_fields['email']['notCorrectly'];
        } elseif (!preg_match("/^[MF]$/", $_POST['sex'])) {
            $system_message = $required_fields['sex']['notCorrectly'];
        } elseif (!$is_programLanguages_correctly) {
            $system_message = $required_fields['programLanguages']['notCorrectly'];
        } elseif (strlen($_POST['bio']) > 66560) {
            $system_message = $required_fields['bio']['notCorrectly'];
        } elseif (empty($_POST['agreement'])) {
            $system_message = $required_fields['agreement']['notCorrectly'];
        } elseif (!preg_match("/^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]+$/", $_POST['birth'])) {
            $system_message = $required_fields['birth']['notCorrectly'];
        } elseif (preg_match("/^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]+$/", $_POST['birth'])) {
            $date = array_map('intval', explode('.', $_POST['birth']));

            // Проверка существования введённой даты
            if ($date[0] < 1 or $date[0] > 31 or $date[1] < 1 or $date[1] > 12 or $date[2] < 1)
                $system_message = $required_fields['birth']['notCorrectly'];
            elseif ($date[2] % 4 != 0 and $date[1] == 2 and $date[0] > 28)
                $system_message = $required_fields['birth']['notCorrectly'];
            elseif ($date[2] % 4 == 0 and $date[1] == 2 and $date[0] > 29)
                $system_message = $required_fields['birth']['notCorrectly'];
            elseif ($date[1] > 1 and $date[1] < 7 and $date[1] % 2 == 0 and $date[0] > 30)
                $system_message = $required_fields['birth']['notCorrectly'];
            elseif ($date[1] > 7 and $date[1] < 12 and $date[1] % 2 == 1 and $date[0] > 30)
                $system_message = $required_fields['birth']['notCorrectly'];

            // Сравнивание с текущей датой
            if ($system_message == '') {
                $cur_date = array_map('intval', explode('/', date('m/d/Y')));

                if ($date[2] > $cur_date[2] or
                    $date[2] == $cur_date[2] and $date[1] > $cur_date[1] or
                    $date[2] == $cur_date[2] and $date[1] == $cur_date[1] and $date[0] > $cur_date[0])
                    $system_message = $required_fields['birth']['notCorrectly'];
                elseif ($date[2] > $cur_date[2] - 14 or $date[2] < $cur_date[2] - 100)
                    $system_message = $required_fields['birth']['outOfRange'];
            }
        }

    }

    if ($system_message != '') {
        echo $system_message;
//        echo "1 " . preg_match("/[^0-9+ ]|\s$|\s\s/", $_POST['phoneNumber']) . " ";
//        echo "2 " . preg_match("/\+{2,}/", $_POST['phoneNumber']) . " ";
//        echo "3 " . preg_match("/\s/", $_POST['phoneNumber']) . " ";
        exit();
    } else {
        echo "Все поля заполнены корректно.";
        print_r($_POST);
    }

//    $host = 'localhost';
//    $username = 'u67319';
//    $password = '6331347';
//    $dbname = 'u67319';
//    $port = 3306;
//    $charset = 'utf8mb4';
//
//    $mysql = new mysqli($host, $username, $password, $dbname, $port);
//    $mysql->set_charset($charset);
//    $mysql->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
//
//    if ($mysql->connect_errno) {
//        echo 'Ошибка подключения: ' . $mysql->connect_error;
//        exit();
//    }
//
//    echo "АЛИЛУЯ";


    $username = 'u67319';
    $password = '6331347';
    $db = new PDO('mysql:host=localhost;dbname=u67319', $username, $password);
//    $dbname = 'u67319';
//    $host = 'localhost';
//    $dsn = 'mysql:host=' . $host . ';port=8000;dbname=' . $dbname . ';charset=utf8';
//    $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname;
//    echo $dsn;

//    try {
//        $db = new PDO('mysql:host=localhost;dbname=u67319', $username, $password);
////        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
////        $db->setAttribute(PDO::ATTR_PERSISTENT, true);
//
//        echo 'Успешное подключение к базе данных.';
//    } catch (PDOException $e) {
//        echo 'Ошибка подключения: ' . $e->getMessage();
//    }
//    echo $db;


}