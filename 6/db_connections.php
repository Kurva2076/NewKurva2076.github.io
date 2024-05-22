<?php

function DBConnect ($action, $user_id = '', $login = '', $pass = '') : array | bool | int
{
    global $my_username, $my_password, $my_dbname;

    $authentication_path = getcwd() . "/server_info.php";
    require $authentication_path;

    $host = 'localhost';
    $dsn = 'mysql:host=' . $host . ';dbname=' . $my_dbname . ';charset=utf8';

    try {
        $db = new PDO($dsn, $my_username, $my_password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_PERSISTENT, true);

        if ($action === 'GetProgramLanguages') {
            $result = GetProgramLanguages($db);
        } elseif (explode(' ', $action)[0] === 'InsertUpdateUserInfo') {
            $loc_action = explode(' ', $action)[1];

            if ($loc_action === 'Insert') {
                InsertUpdateUserInfo($db, $loc_action, $login, $pass);
            } elseif ($loc_action === 'Update') {
                InsertUpdateUserInfo($db, $loc_action);
            } else {
                echo "<script> alert('Передано некорректное значение.'); </script>";
                $result = false;
            }
        } elseif ($action === 'GetUserAuthInfo') {
            $result = GetUserAuthInfo($db);
        } elseif ($action === 'GetUserProgramLanguages') {
            $result = GetUserProgramLanguages($db, $_SESSION['user_id']);
        } elseif ($action === 'GetUserInfo') {
            $result = GetUserInfo($db, $user_id);
        } elseif ($action === 'AdminDataVerification') {
            $result = AdminDataVerification($db, $login, $pass);
        } elseif ($action === 'GetProgramLangsStatistic') {
            $result = GetProgramLangsStatistic($db);
        } elseif ($action === 'GetAllUsersInfo') {
            $result = GetAllUsersInfo($db);
        } elseif ($action === 'DeleteUserInfo') {
            DeleteUserInfo($db, (int) $_GET['delete']);
        } elseif ($action === 'GetUsersCount') {
            $result = GetUsersCount($db);
        } else {
            echo "<script> alert('Передано некорректное значение.'); </script>";
            $result = false;
        }

        unset($db);
        if (isset($result)) {
            return $result;
        }
    } catch (PDOException $e) {
        unset($db);
        $error_message = 'Ошибка подключения к серверу, попробуйте выполнить запрос снова.';
        $error_message = htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8');
        echo "<script> alert(`$error_message`); </script>";
    }

    return true;
}

function GetUsersCount ($database) : int
{
    return (int) $database->query('SELECT COUNT(user_id) FROM Users;')->fetch(PDO::FETCH_COLUMN);
}

function DeleteUserInfo ($database, $user_id) : void
{
    if (!empty(GetUserInfo($database, $user_id))) {
        DeleteUserLangs($database, $user_id);
        DeleteUserAuth($database, $user_id);

        $user_stmt = $database->prepare(
            "DELETE FROM Users WHERE user_id = :user_id;"
        );
        $user_stmt->bindParam(':user_id', $user_id);
        $user_stmt->execute();
    }
}

function DeleteUserAuth ($database, $user_id) : void
{
    $user_authentication = $database->prepare(
        "DELETE FROM Users_Authentication WHERE user_id = :user_id;"
    );
    $user_authentication->bindParam(':user_id', $user_id);
    $user_authentication->execute();
}

function GetAllUsersInfo ($database) : array | bool
{
    $users_info = $database->query('SELECT * FROM Users;')->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($users_info)) {
        for ($i = 0; $i < sizeof($users_info); $i++) {
            $users_info[$i]['program_languages'] = GetUserProgramLanguages($database, (int)$users_info[$i]['user_id']);
            $birth = explode('-', $users_info[$i]['birth']);
            $users_info[$i]['birth'] = $birth[2] . '.' . $birth[1] . '.' . $birth[0];
        }
    }

    return $users_info;
}

function GetProgramLangsStatistic ($database) : array | bool
{
    $statistic = $database->query('SELECT Users_Programming_Languages.language_id, language_name, ' .
        'COUNT(language_name) AS count_users FROM Programming_Languages LEFT JOIN Users_Programming_Languages ' .
        'ON Programming_Languages.language_id = Users_Programming_Languages.language_id ' .
        'GROUP BY Programming_Languages.language_id ORDER BY COUNT(language_name) DESC;')->fetchAll(PDO::FETCH_ASSOC);

    for ($i = 0; $i < sizeof($statistic); $i++) {
        if (is_null($statistic[$i]['language_id']))
            $statistic[$i]['count_users'] = 0;
    }

    return $statistic;
}

function AdminDataVerification ($database, $login, $password) : int
{
    $admin_stmt = $database->prepare('SELECT * FROM Admins WHERE admin_login = :admin_login;');
    $admin_stmt->bindParam(':admin_login', $login);
    $admin_stmt->execute();
    $admin_info = $admin_stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($admin_info)) {
        return 0;
    } elseif (!hash_equals(hash('sha512', $password), $admin_info['admin_password'])) {
        return 0;
    } else {
        return $admin_info['admin_id'];
    }
}

function GetProgramLanguages ($database) : array | bool
{
    return $database->query('SELECT language_name FROM Programming_Languages;')->fetchAll(PDO::FETCH_COLUMN);
}

function GetPostProgramLanguagesIds ($database) : array
{
    $langs = [];
    $program_lang_stmt = $database->prepare(
        'SELECT language_id FROM Programming_Languages WHERE language_name = :language_name;'
    );
    foreach ($_POST['programLanguages'] as $language) {
        $program_lang_stmt->bindParam(':language_name', $language);
        $program_lang_stmt->execute();
        $langs[$language] = $program_lang_stmt->fetch(PDO::FETCH_COLUMN);
    }

    return $langs;
}

function GetUserInfo ($database, $user_id) : array | bool
{
    $user_stmt = $database->prepare('SELECT * FROM Users WHERE user_id = :user_id;');
    $user_stmt->bindParam(':user_id', $user_id);
    $user_stmt->execute();

    $user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user_info)) {
        $user_info['program_languages'] = GetUserProgramLanguages($database, $user_id);
        $birth = explode('-', $user_info['birth']);
        $user_info['birth'] = $birth[2] . '.' . $birth[1] . '.' . $birth[0];
    }

    return $user_info;
}

function GetUserAuthInfo ($database) : array | bool
{
    $user_auth_stmt = $database->prepare('SELECT * FROM Users_Authentication WHERE user_login = :user_login;');
    $user_auth_stmt->bindParam(':user_login', $_POST['login']);
    $user_auth_stmt->execute();

    return $user_auth_stmt->fetch(PDO::FETCH_ASSOC);
}

function GetUserProgramLanguages ($database, $user_id) : array | bool
{
    $lang_stm = $database->prepare(
        "SELECT language_name FROM Users_Programming_Languages LEFT JOIN (Programming_Languages) " .
        "ON (Users_Programming_Languages.language_id = Programming_Languages.language_id) " .
        "WHERE user_id = :user_id;"
    );
    $lang_stm->bindParam(':user_id', $user_id);
    $lang_stm->execute();

    return $lang_stm->fetchAll(PDO::FETCH_COLUMN);
}

function InsertUpdateUserInfo ($database, $action, $login = '', $password = '') : void
{
    $datebirth = explode('.', $_POST['birth']);
    $datebirth = $datebirth[2] . '-' . $datebirth[1] . '-' . $datebirth[0];
    $agreement = ($_POST['agreement'] === 'on') ? 1 : 0;
    $loc_login = $login;
    $loc_password = $password;

    if ($action === 'Update') {
        DeleteUserLangs($database, $_SESSION['user_id']);

        if (!empty($_SESSION['admin_id'])) {
            $user_auth_stmt = $database->prepare('SELECT * FROM Users_Authentication WHERE user_id = :user_id;');
            $user_auth_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $user_auth_stmt->execute();

            $user_auth_info = $user_auth_stmt->fetch(PDO::FETCH_ASSOC);

            $loc_login = $user_auth_info['user_login'];
            $loc_password = $user_auth_info['user_password'];

            DeleteUserAuth($database, $_SESSION['user_id']);
        }
    }

    if ($action === 'Insert') {
        $query = "INSERT INTO Users (full_name, birth, sex, phone_number, e_mail, bio, agreement) " .
            "VALUES (:full_name, :birth, :sex, :phone_number, :e_mail, :bio, :agreement);";
    } else {
        $query = "UPDATE Users SET user_id = :new_user_id, full_name = :full_name, birth = :birth, sex = :sex, " .
            "phone_number = :phone_number, e_mail = :e_mail, bio = :bio, agreement = :agreement " .
            "WHERE user_id = :user_id;";
    }

    $users_stmt = $database->prepare($query);
    $users_stmt->bindParam(':full_name', $_POST['fullName']);
    $users_stmt->bindParam(':birth', $datebirth);
    $users_stmt->bindParam(':sex', $_POST['sex']);
    $users_stmt->bindParam(':phone_number', $_POST['phoneNumber']);
    $users_stmt->bindParam(':e_mail', $_POST['email']);
    $users_stmt->bindParam(':bio', $_POST['bio']);
    $users_stmt->bindParam(':agreement', $agreement);

    if ($action === 'Update') {
        $new_user_id = (!empty($_SESSION['admin_id'])) ? (int) $_POST['userID'] : $_SESSION['user_id'];

        $users_stmt->bindParam(':new_user_id', $new_user_id);
        $users_stmt->bindParam(':user_id', $_SESSION['user_id']);
        $users_stmt->execute();

        $_SESSION['user_id'] = $new_user_id;
    } else {
        $users_stmt->execute();
    }

    $user_id = ($action === 'Update') ? $_SESSION['user_id'] :
        GetUserInfo($database, $database->lastInsertId('Users'))['user_id'];

    $langs = GetPostProgramLanguagesIds($database);

    $users_program_lang_stmt = $database->prepare(
        "INSERT INTO Users_Programming_Languages (user_id, language_id) " .
        "VALUES (:user_id, :language_id)"
    );
    foreach ($langs as $language_id) {
        $users_program_lang_stmt->bindParam(':user_id', $user_id);
        $users_program_lang_stmt->bindParam(':language_id', $language_id);
        $users_program_lang_stmt->execute();
    }

    if ($action === 'Insert') {
        InsertUserAuth($database, $user_id, $loc_login, hash('sha512', $loc_password));
    } elseif (!empty($_SESSION['admin_id'])) {
        InsertUserAuth($database, $user_id, $loc_login, $loc_password);
    }
}

function InsertUserAuth ($database, $user_id, $login, $password) : void
{
    $users_auth_stmt = $database->prepare(
        "INSERT INTO Users_Authentication (user_id, user_login, user_password) " .
        "VALUES (:user_id, :user_login, :user_password)"
    );
    $users_auth_stmt->bindParam(':user_id', $user_id);
    $users_auth_stmt->bindParam(':user_login', $login);
    $users_auth_stmt->bindParam(':user_password', $password);
    $users_auth_stmt->execute();
}

function DeleteUserLangs ($database, $user_id) : void
{
    $users_program_langs = $database->prepare(
        "DELETE FROM Users_Programming_Languages WHERE user_id = :user_id;"
    );
    $users_program_langs->bindParam(':user_id', $user_id);
    $users_program_langs->execute();
}