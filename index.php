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
// tries to launch corresponding action
$fc = new FrontController($_REQUEST);


/*switch ($_REQUEST['action']){
    case 'showAdminPage':
        $ctrl = UserController::getInstance();
        $ctrl->setView(new \stdClass());
        $view = $ctrl->showAdminPage();
        if (!empty($view->isAdmin)){
            $view->content = renderView('admin', $view);
        } else {
            $view->content = renderView('message', $view);
        }
        render($view);
        break;
    case 'addUser':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        $request = new Request();
        $request->setParams(array(
            'name' => isset($_REQUEST['name']) ? $_POST['name'] : null,
            'email' => isset($_REQUEST['email']) ? $_POST['email'] : null,
            'type' => isset($_REQUEST['type']) ? $_REQUEST['type'] : null,
            'role' => isset($_REQUEST['role']) ? $_POST['role'] : null,
            'password' => isset($_REQUEST['password']) ? $_REQUEST['password'] : null
            ));
        $ctrl = UserController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        try {
            $view = $ctrl->addUser();
        } catch (\Exception $ex) {
            $view->message = $ex->getMessage();
            require_once 'surveyzilla/application/view/message.php';
        }
        require_once 'surveyzilla/application/view/message.php';
        break;
    case 'updateUser':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        $request = new Request();
        $request->setParams(array(
            'id' => isset($_POST['id']) ? $_POST['id'] : null,
            'name' => isset($_POST['name']) ? $_POST['name'] : null,
            'email' => isset($_POST['email']) ? $_POST['email'] : null,
            'password' => isset($_POST['password']) ? $_POST['password'] : null
            ));
        $ctrl = UserController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->updateUser();
        require_once 'surveyzilla/application/view/message.php';
        break;
    case 'deleteUser':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        $request = new Request();
        $request->setParam('id',isset($_REQUEST['id']) ? $_REQUEST['id'] : null);
        $ctrl = UserController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->deleteUser();
        require_once 'surveyzilla/application/view/message.php';
        break;
    case 'displayUser':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        $request = new Request();
        $request->setParam('id',isset($_REQUEST['id']) ? $_REQUEST['id'] : null);
        $ctrl = UserController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->displayUser();
        if ($view->isAuthorized){
            require_once 'surveyzilla/application/view/displayUser.php';
        } else {
            require_once 'surveyzilla/application/view/message.php';
        }
        break;
    case 'displayAllUsers':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        $ctrl = UserController::getInstance();
        //$ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->displayAllUsers();
        if ($view->isAuthorizedAdmin){
            require_once 'surveyzilla/application/view/displayAllUsers.php';
        } else {
            require_once 'surveyzilla/application/view/message.php';
        }
        break;
    case 'addPoll':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        ini_poll();
        $request = new Request();
        $request->setParams(array(
            'name' => isset($_REQUEST['name']) ? filter_input(INPUT_POST, 'name',
                    FILTER_SANITIZE_SPECIAL_CHARS) : null
            ));
        $ctrl = PollController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->addPoll();
        require_once 'surveyzilla/application/view/message.php';
        break;
    case 'addItem':
        require_once 'surveyzilla/application/view/header.php';
        ini_user();
        ini_poll();
        $request = new Request();
        $request->setParams(array(
            'pollId' => isset($_POST['pollId']) ? $_POST['pollId'] : null,
            'question' => isset($_POST['question']) 
                ? filter_input(INPUT_POST, 'question', FILTER_SANITIZE_SPECIAL_CHARS) 
                : null,
            'optionsType' => isset($_POST['optionsType']) ? $_POST['optionsType'] : null,
            'hasCustomField' => isset($_POST['hasCustomField']) ? $_POST['hasCustomField'] : null,
            'optionsArr' => isset($_POST['optionsArr']) ? filter_var_array($_POST['optionsArr'], FILTER_SANITIZE_SPECIAL_CHARS) : null
            ));
        $ctrl = PollController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->addItem();
        require_once 'surveyzilla/application/view/message.php';
        break;
    case 'displayPoll':
        require_once 'surveyzilla/application/view/header.php';
        ini_poll();
        $request = new Request();
        $request->setParam('id',$_REQUEST['id']);
        $ctrl = PollController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $view = $ctrl->displayPoll();
        require_once 'surveyzilla/application/view/displayPoll.php';
        break;
    case 'run':
        $request = new Request();
        $request->setParams(array(
            'pollId' => isset($_REQUEST['poll']) ? filter_var($_REQUEST['poll'], FILTER_VALIDATE_INT): null,
            'token' => isset($_COOKIE['token']) ? $_COOKIE['token'] : null,
            'itemId' => isset($_REQUEST['item']) ? filter_var($_REQUEST['item'], FILTER_VALIDATE_INT) : null,
            'customOption' => isset($_REQUEST['customOption']) ? filter_var($_REQUEST['customOption'], FILTER_SANITIZE_SPECIAL_CHARS) : null,
            'options' => isset($_REQUEST['options']) ? filter_var_array($_REQUEST['options'], FILTER_SANITIZE_SPECIAL_CHARS) : null
            ));
        $ctrl = PollController::getInstance();
        $ctrl->setRequest($request);
        $view = $ctrl->runPoll();
        if (isset($view->message)) {
            $view->content = renderView('message', $view);
            render($view, 'runPoll');
            break;
        } else {
            $view->content = renderView('runPoll', $view);
            render($view, 'runPoll');
            break;
        }
}*/