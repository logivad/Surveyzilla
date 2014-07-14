<?php
use surveyzilla\application\view\UI;
?>
<div class="mainmenu">
    <a href="index.php"><?php echo UI::$lang['main_page'] ?></a>
    <?php
    if (!empty($view->isAuthorized)) {
        echo '<a href="index.php?a=account">' . UI::$lang['account'] . '</a> ';
    }
    if (!empty($view->isAdmin)) {
        echo '<a href="index.php?a=admin">' . UI::$lang['admin_page'] . '</a> ';
    }
    if (!empty($view->isAuthorized)) {
        echo '<a href="index.php?a=logoff">' . UI::$lang['log-off'] . '</a> ';
    } else {
        echo '<a href="index.php?a=login">' . UI::$lang['log-in'] . '</a> ';
    }
    ?>
</div>
