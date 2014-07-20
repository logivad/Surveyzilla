<?php
use surveyzilla\application\view\UI;
?>
<div class="content">
    <form name="auth" action="index.php?a=login" method="POST">
        e-mail
        <input class="field" type="text" name="email" />
        <?php echo UI::$lang['password'] ?>
        <input class="field" type="password" name="password" />
        <p id="btn"><input class="btn" type="submit" value="<?php echo UI::$lang['log-in'] ?>" /></p>
    </form>
</div>
<?php
if (!empty($view->message)) {
    echo "<div class='msg'>{$view->message}</div>";
}
?>