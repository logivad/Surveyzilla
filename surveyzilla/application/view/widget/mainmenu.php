<div class="mainmenu">
    <a href="index.php">Главная</a>
    <?php
    if (!empty($view->isAuthorized)) {
        echo '<a href="index.php?action=account">Личный кабинет</a> ';
    }
    if (!empty($view->isAdmin)) {
        echo '<a href="index.php?action=showAdminPage">Админ-панель</a> ';
    }
    if (!empty($view->isAuthorized)) {
        echo '<a href="index.php?action=quit">Выйти</a> ';
    } else {
        echo '<a href="index.php?action=authorize">Войти</a> ';
    }
    ?>
</div>
