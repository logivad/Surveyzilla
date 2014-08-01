<?php

namespace surveyzilla\application;
use surveyzilla\application\controller\Application;

date_default_timezone_set('Europe/Kiev');

// Autoloader (each class is located in a directory according to it's namespace)
function autoload($className){
    require_once str_replace('\\','/',$className).'.php';
}
spl_autoload_register('surveyzilla\application\autoload');
$sz = new Application($_REQUEST);
$sz->setLanguage(Config::$lang);
$sz->launchAction();