<?php

$db_connections_path = getcwd() . "/db_connections.php";
require_once $db_connections_path;

if (empty($_GET['user_id'])) {
    $users_info = DBConnect('GetAllUsersInfo');

    if (empty($users_info)) {
        $users_info = array();
    }
} else {
    $users_info = array(DBConnect('GetUserInfo', user_id: $_GET['user_id']));
}

$cnt_users = (!empty($_GET['cnt_user_on_page']) and preg_match("/^[1-9][0-9]?$/", $_GET['cnt_user_on_page'])) ?
    $_GET['cnt_user_on_page'] : sizeof($users_info);
$page_num = (!empty($_GET['page'])) ? $_GET['page'] : 1;
$cnt_pages = ($cnt_users == sizeof($users_info)) ? 1 : floor(sizeof($users_info) / $cnt_users);
$task_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$task_url = substr($task_url, 0, strripos($task_url, '/'));

?>

<table id="usersTable" class="table table-bordered">
    <tr class="table-name-row">
        <th>ID</th>
        <th>ФИО</th>
        <th>Дата рождения</th>
        <th>Пол</th>
        <th>Телефон</th>
        <th>E-mail</th>
        <th>Биография</th>
        <th>ЯП</th>
        <th colspan="2">Действия</th>
    </tr>

    <?php for ($i = ($page_num - 1) * $cnt_users; (($i < $page_num * $cnt_users and $page_num != $cnt_pages) or
        ($i < sizeof($users_info) and $page_num == $cnt_pages)); $i++): ?>
        <?php if (!empty($users_info[$i])): ?>
            <tr class="table-row">
                <?php

                foreach ($users_info[$i] as $name => $value) {
                    if ($name !== 'agreement') {
                        print('<th>');

                        if ($name === 'program_languages') {
                            foreach ($value as $lang) {
                                print $lang . '<br>';
                            }
                        } elseif ($name === 'full_name' or $name === 'bio') {
                            $value_elems = explode(' ', $value);
                            for ($j = 0; $j < sizeof($value_elems); $j++) {
                                print $value_elems[$j] . (($j == sizeof($value_elems) - 1) ? '' : '<br>');
                            }
                        } else {
                            print $value;
                        }

                        print('</th>');
                    }
                }

                ?>

                <th class="delete-cell">
                    <div class="delete-button">
                        <a href="admin.php?delete=<?php print $users_info[$i]['user_id'] ?>">Удалить</a>
                    </div>
                </th>
                <th class="edit-cell">
                    <div class="edit-button">
                        <a href="<?php print $task_url . '/form.php?admins_edit=' . $users_info[$i]['user_id'] ?>">
                            Редактировать
                        </a>
                    </div>
                </th>
            </tr>
        <?php endif; ?>
    <?php endfor; ?>
</table>