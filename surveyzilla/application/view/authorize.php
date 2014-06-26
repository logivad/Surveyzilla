        <div id="border" style="width:160px;">
            <form name="authorize" action="index.php?action=authorize" method="POST">
                <p>E-mail <input type="text" name="email" />
                <p>Пароль <input type="password" name="password" />
                <p><input type="submit" value="войти" /></p>
            </form>
        </div>
        <p><?= $view->message ?></p>