<div class="mainmenu">
    <a href="index.php">Главная</a>
    <?php
    if (!empty($view->isAuthorized)) {
        echo '<a href="index.php?a=account">Личный кабинет</a> ';
    }
    if (!empty($view->isAdmin)) {
        echo '<a href="index.php?a=admin">Админ-панель</a> ';
    }
    if (!empty($view->isAuthorized)) {
        echo '<a href="index.php?a=logoff">Выйти</a> ';
    } else {
        echo '<a href="index.php?a=login">Войти</a> ';
    }
    ?>
</div>
