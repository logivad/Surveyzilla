<?php
namespace surveyzilla\application\controller;

use stdClass;
use surveyzilla\application\Config;
use surveyzilla\application\controller\PollController;
use surveyzilla\application\controller\UserController;
use surveyzilla\application\model\Request;
use surveyzilla\application\model\View;
use surveyzilla\application\view\UI;

/**
 * 
 */
class Application
{
    // Request object, contains params from $_REQUEST
    private $request;
    
    public function __construct($request) {
        $this->request = new Request($request);
    }
    /**
     * Takes a name of the desired view (from application/view) and an object
     * of class View which contains data that is used in that view 
     * (e.g. $userName, $title etc.). Output of this function is then inserted
     * into layout (method renderPage) and is sent to the browser.
     * 
     * @param   string $viewName Determines which view to render
     * @param   obj $view Object of a class View wich contains data for a view
     * @return  string Returns rendered view that is ready to be inserted
     *          into template
     */
    private function renderView($viewName, $view=null) {
        // Если не найден файл вида, выдаем сообщение об ошибки и ответ 403
        if (!file_exists("surveyzilla/application/view/$viewName.php")) {
            http_response_code(404);
            $view = new stdClass();
            $view->message = 'Странно.. страница не отобразилась';
            return $this->renderView('notification', $view);
        }
        // Формируем содержание страницы (будет включено в шаблон)
        ob_start();
        require_once "surveyzilla/application/view/$viewName.php";
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    /**
     * Final function for rendering a page. Takes rendered view from renderView()
     * and inserts it into desired layout. The output of this function can be
     * sent to the browser.
     * 
     * @param obj $view Object of a class View wich contains data for a view
     * @param type $layoutName Name of used layout from application/layout
     */
    private function renderPage($view, $layoutName=null) {
    if (isset($layoutName) 
        && file_exists("surveyzilla/application/layout/$layoutName.php")) {
            require_once "surveyzilla/application/layout/$layoutName.php";
        } else {
            require_once "surveyzilla/application/layout/default.php";
        }
    }
    /**
     * When user types in a address bar
     * 
     *      http://surveyzilla.ru/index.php?action=account
     * 
     * this function searches for a method actionAccount(). If it is found, it
     * launces, if not - user gets 'not found' message
     * 
     */
    public function launchAction() {
        if ($this->request->isSetParam('a')) {
            $actionName = $this->request->get('a');
        } elseif ($this->request->isSetParam('stat')) {
            // Shortcut for statistics
            $actionName = 'stat';
            $this->request->set('poll', $this->request->get('stat'));
        } else {
            $actionName = 'main';
        }
        $actionName = 'action'.strtoupper($actionName[0]).substr($actionName, 1);
        if (method_exists($this, $actionName)) {
            $this->$actionName();
        } else {
            $view = new View();
            http_response_code(404);
            $view->title = 'Not found';
            $view->content = $this->renderView('404');
            $this->renderPage($view, '404');
        }
    }
    /**
     * 
     * @param type $lang Language to use ('ru')
     */
    public function setLanguage($lang) {
        UI::setLang($lang);
    }
    /**
     * Main page of the website
     */
    private function actionMain() {
        $ctrl = UserController::getInstance();
        // Methods of the Controller return filled View objects
        $view = $ctrl->showMainPage();
        // These Vies objects are used to render views (thay store needed data
        // such as $userName etc.)
        $view->content = $this->renderView('main');
        // Rendered view (content) is inserted into layout and send to the browser
        $this->renderPage($view);
    }
    /**
     * Authorization
     */
    private function actionLogin() {
        $this->request->filterEmail();
        $this->request->filterPassword();
        $ctrl = UserController::getInstance();
        $ctrl->setRequest($this->request);
        $view = $ctrl->authorize();
        if (true === $view->isAuthorized){
            // Если уже авторизован, отправляем на личную страницу
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/index.php?a=account");
            exit;
        } else {
            // Если не авторизован (зашел первый раз, неверно ввел данные и т.д.),
            // отобразим страницу входа еще раз
            $view->content = $this->renderView('login', $view);
            $view->title= UI::$lang['log-in'];
            $this->renderPage($view, 'notification');
        }
    }
    /**
     * Logging off
     */
    private function actionLogoff() {
        $ctrl = UserController::getInstance();
        $view = $ctrl->authorize(true);
        if ($view->loggedOff){
            // User logged off, redirecting to log-in page
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/index.php?a=login");
        } else {
            // Controller failed, something wrong
            $view = new View();
            $view->title = $view->message = UI::$lang['error'];
            $view->errorCode = 'E0001';
            $view->content = $this->renderView('notification', $view);
            $this->renderPage($view, 'notification');
        }
    }
    /**
     * User's account page. This action is automatically launced on successful
     * authorization in actionLogin()
     */
    private function actionAccount() {
        $ctrl = UserController::getInstance();
        $view = $ctrl->showAccount();
        if (true === $view->isAuthorized){
            $view->content = $this->renderView('account', $view);
            $this->renderPage($view);
        } else {
            // Если пользователь не авторизован, перенаправим на страницу входа
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/index.php?a=login");
        }
    }
    /**
     * Runs the poll.
     * 
     * Firstly, user receives a token. This token is sent to the server with every
     * answer. Answers are stored in a dedicated table AnswerTemp untill poll
     * is finished. When poll is finished (answered), records are deleted and
     * answers are added to poll results.
     * 
     * Parameters for this action:
     * 
     *      poll        - poll Id in database
     *      custopt     - user's custom option
     *      opts        - selected options (array)
     */
    private function actionRun() {
        $this->request->filterPollRunParams();
        $ctrl = PollController::getInstance();
        $ctrl->setRequest($this->request);
        $view = $ctrl->runPoll();
        if (isset($view->message)) {
            $view->content = $this->renderView('notification', $view);
        } elseif (isset ($view->item->isFinal) && $view->item->isFinal == true) {
            $view->content = $this->renderView('runFinal', $view);
        } elseif (isset ($view->stat)) {
            // Poll is finished, time to show statistics
            header('Location: http://' . Config::$domain 
            . "/index.php?a=stat&poll={$view->pollId}");
            //$view->content = $this->renderView('runStat', $view);
        } else {
            $view->content = $this->renderView('runNormal', $view);
        }
        $this->renderPage($view, 'run');
    }
    /**
     * Display poll statistics
     */
    private function actionStat() {
        $ctrl = PollController::getInstance();
        $this->request->filterStat();
        $ctrl->setRequest($this->request);
        $view = $ctrl->getStat();
        if (isset($view->message)) {
            $view->content = $this->renderView('notification', $view);
        } else {
            $view->content = $this->renderView('runStat', $view);
        }
        $this->renderPage($view, 'run');
    }
}