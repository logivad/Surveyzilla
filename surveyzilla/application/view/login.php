<?php
use surveyzilla\application\view\UI;
?>
<div class="content">
    <div class="login">
        <div class="form">
            <form name="auth" action="index.php?a=login" method="POST">
                e-mail
                <input class="field" type="text" name="email" />
                <?php echo UI::$lang['password'] ?>
                <input type="password" name="password" />
                <div style="text-align: right; padding-top: 10px;">
                    <input type="submit" value="<?php echo UI::$lang['log-in'] ?>" />
                </div>
            </form>
        </div>
        <?php
        if (!empty($view->message)) {
            echo "<div class='msg'>{$view->message}</div>";
        }
        ?>
    </div>
</div>