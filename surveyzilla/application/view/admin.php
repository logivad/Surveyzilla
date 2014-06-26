
        <h1>Surveyzilla.ru</h1>
        <h2>Доступные команды</h2>
        <div id="border">
            <form name="display_user" action="index.php?action=displayUser" method="POST">
                <p><b>Отобразить данные пользователя</b> (<a href="index.php?action=displayAllUsers">список пользователей</a>)</p>
                <p>ID пользователя: <input type="text" name="id" value="0"/>
                    <input type="submit" /></p>
            </form>
        </div>
       <div id="border">
            <form name="add_user" action="index.php?action=addUser" method="POST">
                <p><b>Добавить пользователя</b></p>
                <p>Логин <input type="text" name="name" value="vasya" /> 
                Пароль <input type="password" name="password" value="123456789" /> 
                E-mail <input type="email" name="email" value="vasya@example.com" /></p>
                <p>Тип пользователя <input type="text" name="type" value="0"/> 
                Тарифный план
                    <select name="role">
                        <option value="1">ADMIN</option>
                        <option value="2">FREE</option>
                        <option value="4">GOLD</option>
                        <option value="8">PLATINUM</option>
                        <option value="16">TEMP</option>
                    </select>
                </p>
                <p><input type="submit" /></p>
            </form>
        </div>
        <div id="border">
            <form name="delete_user" action="index.php?action=deleteUser" method="POST">
                <p><b>Удалить пользователя</b> (<a href="index.php?action=deleteAllUsers">удалить всех</a>)</p>
                <p>ID пользователя: <input type="text" name="id" value="0"/>
                    <input type="submit" /></p>
            </form>
        </div>
       <div id="border">
            <form name="update_user" action="index.php?action=updateUser" method="POST">
                <p><b>Редактировать пользователя</b></p>
                <p>ID <input type="text" name="id" value="0" /> 
                <p>Логин <input type="text" name="name" value="admin" /> 
                Пароль <input type="password" name="password" value="12345" /> 
                E-mail <input type="email" name="email" value="vasya@example.com" /></p>
                <!--Тарифный план
                    <select name="role">
                        <option value="1">ADMIN</option>
                        <option value="2">FREE</option>
                        <option value="4">GOLD</option>
                        <option value="8">PLATINUM</option>
                        <option value="16">TEMP</option>
                    </select-->
                <p><input type="submit" /></p>
            </form>
        </div>
        <div id="border">
            <form name="display_poll" action="index.php?action=displayPoll" method="POST">
                <p><b>Отобразить опрос</b></p>
                <p>ID опроса: <input type="text" name="id" value="0" />
                    <input type="submit" /></p>
            </form>
        </div>
        <div id="border">
            <form name="add_poll" action="index.php?action=addPoll" method="POST">
                <p><b>Добавить опрос</b></p>
                <!--p>ID создателя: <input type="text" name="creatorId" value="0"/></p-->
                <p>Название опроса <input type="text" name="name" value="0"/></p>
                <p><input type="submit" /></p>
            </form>
        </div>
        <div id="border">
            <form name="add_item" action="index.php?action=addItem" method="POST">
                <p><b>Создать вопрос</b></p>
                <p>ID опроса: <input type="text" name="pollId" value="0" /></p>
                <p>Вопрос<input type="text" size="60" name="question" value="Какие деревья Вы любите?" /></p>
                <p>Тип элемента
                    <select name="optionsType">
                        <option value="radio">radio</option>
                        <option value="checkbox">checkbox</option>
                    </select>
                </p>
                <p>Разрешен ли индивидуальный вариант ответа (0/1)<input type="text" name="hasCustomField" value="0" /></p>
                <p>Варианты ответа:<br />
                    <input type="text" name="optionsArr[0]" value="Дуб" /><br />
                    <input type="text" name="optionsArr[1]" value="Ёлочка" /><br />
                    <input type="text" name="optionsArr[2]" value="Сосна" /><br />
                    <input type="text" name="optionsArr[3]" value="Берёза" /><br />
                </p>
                <p><input type="submit" /></p>
            </form>
        </div>