<?php
/**
 * Front controller
 * 
 */
namespace surveyzilla\application;
date_default_timezone_set('Europe/Kiev');
function autoload($className){
    require_once str_replace('\\','/',$className).'.php';
}
spl_autoload_register('surveyzilla\application\autoload');

use surveyzilla\application\service\UserService,
    surveyzilla\application\service\PollService,
    surveyzilla\application\model\Request,
    surveyzilla\application\controller\UserController,
    surveyzilla\application\controller\PollController,
    surveyzilla\application\dao\UserDAOMySQL;
/**
 * Функция для рендеринга вида. Принимает аргумент - имя вида, без расширения
 * Используется для чтения вида в переменную $view->contents для дальнейшего
 * включения в шаблон (по умолчанию - layout/default.php)
 */
function renderView($viewName, $view=null) {
    // Если не найден файл вида, выдаем сообщение об ошибки и ответ 403
    if (!file_exists("surveyzilla/application/view/$viewName.php")) {
        http_response_code(404);
        $view = new \stdClass();
        $view->message = 'Странно.. страница не отобразилась';
        return renderView('message', $view);
    }
    // Формируем содержание страницы (будет включено в шаблон)
    ob_start();
    require_once "surveyzilla/application/view/$viewName.php";
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
/**
 * Функция для конечного отображения страницы. Использует шаблон с именем,
 * указанным в параметре $layoutName (если задан). По умолчанию используется
 * шаблон default. В шаблон должен быть передан:
 *      $view - объект, содержащий необходимую странице информацию.
 * Он должен содержать:
 *      $viw->content - содержимое страницы, сгенерированное функцией renderView
 */
function render($view, $layoutName=null) {
    if ($layoutName && file_exists("surveyzilla/application/layout/$layoutName.php")) {
        require_once "surveyzilla/application/layout/$layoutName.php";
    } else {
        require_once "surveyzilla/application/layout/default.php";
    }
}
// Действие, выполняемое по умолчанию - отображение главной страницы сайта
if (empty($_REQUEST['action'])){
    // Получаем указатель на объект контроллера
    $ctrl = UserController::getInstance();
    // Метод контроллера вернет объект с необходимыми для вида пеерменными
    $view = $ctrl->showMainPage();
    // Рендеринг вида,  используем полученные переменные из $view
    $view->content = renderView('main');
    // Конечный рендеринг страницы (вид внедряется в шаблон), вывод на экран
    render($view);
    exit();
}
switch ($_REQUEST['action']){
    case 'showAdminPage':
        ini_user();
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
    case 'account':
        $ctrl = UserController::getInstance();
        $ctrl->setView(new \stdClass());
        $view = $ctrl->showAccount();
        if (true === $view->isAuthorized){
            $view->content = renderView('account', $view);
        } else {
            // Если пользователь не авторизован, перенаправим на страницу входа
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/index.php?action=authorize");
            exit;
        }
        render($view);
        break;
    case 'authorize':
        $request = new Request();
        $request->setParams(array(
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'password' => filter_input(INPUT_POST, 'password', FILTER_VALIDATE_REGEXP,
                    array('options'=>array('regexp'=>'/[a-zA-Z0-9_!-.]{6,}/')))
            ));
        $ctrl = UserController::getInstance();
        $ctrl->setRequest($request);
        $view = $ctrl->authorize();
        if (true === $view->isAuthorized){
            // Если уже авторизован, отправляем на личную страницу
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/index.php?action=account");
            exit;
        } else {
            // Если не авторизован (зашел первый раз, неверно ввел данные и т.д.),
            // отобразим страницу входа еще раз
            $view->content = renderView('authorize', $view);
        }
        render($view);
        break;
    case 'quit':
        $ctrl = UserController::getInstance();
        $view = $ctrl->authorize(true);
        if ($view->loggedOff){
            // Пользователь вышел, перенаправляем на страницу входа
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/index.php?action=authorize");
            exit;
        } else {
            $view->message = 'Ошибка!';
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
        /**$request = new Request();
        $request->setParam('id',$_POST['id']);*/
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
        ini_user();
        ini_poll();
        $request = new Request();
        $request->setParams(array(
            'pollId' => isset($_REQUEST['pollId']) ? filter_var($_REQUEST['pollId'], FILTER_VALIDATE_INT): null,
            'token' => isset($_COOKIE['token']) ? $_COOKIE['token'] : null,
            'itemId' => isset($_REQUEST['itemId']) ? filter_var($_REQUEST['itemId'], FILTER_VALIDATE_INT) : null,
            'customOption' => isset($_REQUEST['customOption']) ? filter_var($_REQUEST['customOption'], FILTER_SANITIZE_SPECIAL_CHARS) : null,
            'options' => isset($_REQUEST['options']) ? filter_var_array($_REQUEST['options'], FILTER_SANITIZE_SPECIAL_CHARS) : null
            ));
        $ctrl = PollController::getInstance();
        $ctrl->setRequest($request);
        $ctrl->setView(new \stdClass());
        $ctrl->runPoll();
        break;
    case 'help':
        require_once 'surveyzilla/application/view/header.php';
        require_once 'surveyzilla/application/help/help.php';
        break;
    default;
        // Page not found!
        // Sending a proper response and showing 404 message
        http_response_code(404);
        $view = new \stdClass();
        $view->title = 'Not found';
        $view->content = renderView('404');
        render($view);
        exit();
}