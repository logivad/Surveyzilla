<?php

namespace surveyzilla\application;
use surveyzilla\application\controller\FrontController;

date_default_timezone_set('Europe/Kiev');

// Autoloader (each class is located in a directory according to it's namespace)
function autoload($className){
    require_once str_replace('\\','/',$className).'.php';
}
spl_autoload_register('surveyzilla\application\autoload');

// Front controller receives $_REQUEST, looks at it's 'a' (action) value and
// launches corresponding action
$fc = new FrontController($_REQUEST);