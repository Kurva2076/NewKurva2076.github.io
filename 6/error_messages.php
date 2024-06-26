<?php

global $purpose;

if (strcmp($purpose, 'form') == 0) {
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

    if (!empty($_SESSION['admin_id'])) {
        $error_messages['userID'] = [
            400 => 'Введите ID пользователя.',
            403 => 'ID может содержать только цифры.'
        ];
    }
} elseif (strcmp($purpose, 'authorization') == 0) {
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
}