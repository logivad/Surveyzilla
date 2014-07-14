<pre>
<?php if (empty($view->usersArr)){ ?>
+--------------+
| Nobody here! |
+--------------+
<?php } else {
    foreach ($view->usersArr as $userData){
        echo $userData.'<br />';
    }
} ?>
</pre>