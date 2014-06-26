<?php
use surveyzilla\application\model\user\Role;

if ($view->user === false){ ?>
<div id="message">
    User not found!
</div>
<?php } else { ?>
<pre>
id:            <?= $view->user->getId().PHP_EOL ?>
name:          <?= $view->user->getName().PHP_EOL ?>
email:         <?= $view->user->getEmail().PHP_EOL ?>
role(s):       <?= Role::getInstance()->getAllRolesStr($view->user->getRoleset()).PHP_EOL?>
type:          <?= $view->user->getTypeStr().PHP_EOL ?>
password:      <?= $view->user->getPassword().PHP_EOL ?>
hash:          <?= $view->user->getHash().PHP_EOL ?>
polls:         <?= $view->user->getPollList().PHP_EOL ?>
privileges:

<?php
$indices = $view->privileges->getPrivilegesArr();
foreach ($indices as $key => $val){
    echo "&nbsp;&nbsp;&nbsp;&nbsp;$key:";
    for ($i=20-strlen($key); $i>0; $i--){    // пробелы для ровного отступа значений
        echo ' ';
    }
    echo $val.PHP_EOL;
}
}
?>
</pre>
