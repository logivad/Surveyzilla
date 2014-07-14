        <div id="box">
            <form name="authorize" action="index.php?a=login" method="POST">
                <p>E-mail<br /><input type="text" name="email" />
                <p>Пароль<br /><input type="password" name="password" />
                <p><input type="submit" value="<?php echo \surveyzilla\application\view\UI::$lang['log-in'] ?>" /></p>
            </form>
        </div>
        <p><?= $view->message ?></p>