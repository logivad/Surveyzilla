<?php
namespace surveyzilla\application\controller;

use surveyzilla\application\model\user\Privileges;
use surveyzilla\application\model\user\User;
use surveyzilla\application\service\UserService;
use surveyzilla\application\view\UI;
use surveyzilla\application\model\View;
class UserController
{
    private $service;
    // Объект, содержащий входные параметры для функций контроллера:
    private $request;
    // Объект для хранения необходимых для вида переменных:
    private $view;
    private static $_instance;
    private function __construct(){
        $this->view = new View();
    }
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->service = UserService::getInstance();
        }
        return self::$_instance;
    }
    public function setRequest($request){
        $this->request = $request;
    }
    public function setView($view){
        $this->view = $view;
    }
    public function displayUser(){
        // Операцию может выполнить только соответствующий пользователь и админ,
        // причем пользователь может просмотреть лишь свои данные
        $user = $this->service->isAuthorized();
        if (false !== $user){
            if ($user->isAdmin()){
                // В запросе может и не быть id, тогда отображаем информацию о самом пользователе
                if ($this->request->isSetParam('id')){
                    $id = $this->request->getParam('id');
                } else {
                    $id = $user->getId();
                }
                $this->view->user = $this->service->findUserById($id);
                $this->view->privileges = $this->service->findUserPrivilegesById($id);
                $this->view->isAuthorized = true;
            } else {
                $this->view->user = $user;
                $this->view->privileges = $this->service->findUserPrivilegesById($user->getId());
                $this->view->isAuthorized = true;
            }
        } else {
            $this->view->isAuthorized = false;
            $this->view->message = UI::$text['acces_denied'];
        }
        return $this->view;
    }
    public function displayAllUsers(){
        if ($this->service->isAuthorizedAdmin()){
            $this->view->isAdmin = true;
            $this->view->usersArr = $this->service->getAllUsers();
            return $this->view;
        } else {
            $this->view->isAdmin = false;
            $this->view->message = UI::$text['acces_denied'];
        }
        return $this->view;
    }
    public function deleteUser(){
        // Админ не может удалить себя
        // Обычный пользователь может удалить лишь самого себя
        // Админ не может удалить другого админа
        $user = $this->service->isAuthorized();
        $userPrey = $this->service->findUserById($this->request->getParam('id'));
        if ($user === false
            || $userPrey === false
            || !$user->isAdmin() && $this->request->getParam('id') !== $user->getId()
            ){
            $this->view->message = UI::$text['acces_denied'];
            return $this->view;
        }
        $this->view->message = $this->service->deleteUser($this->request->getParam('id')) ? UI::$text['success'] : UI::$text['error'];
        return $this->view;
    }
    public function showAdminPage(){
        $this->view->title = 'Admin page';
        $user = $this->service->isAuthorized();
        if ($user && $user->isAdmin()){
            $this->view->isAdmin = true;
            $this->view->isAuthorized = true;
            $this->view->userName = $user->getName();
        } else {
            http_response_code(403);
            $this->view->message = UI::$text['acces_denied'];
        }
        return $this->view;
    }
    public function authorize($quit=false){
        if ($quit) {
            if ($this->service->quit()){
                $this->view->loggedOff = true;
            }
            // Если фронт-контроллер не найдет $loggedOff, отобразит ошибку
            return $this->view;
        }
        // Если неавторизованный пользователь впервые зашел на страницу авторизации
        if (!$this->request->isSetParam('email') || 
                !$this->request->isSetParam('password')){
            $this->view->isAuthorized = false;
            $this->view->message = '';
            $this->view->title = UI::$text['log-in'];
            return $this->view;
        }
        // Если пользователь отправил данные для авторизации
        if ($this->service->authorize($this->request->getParam('email'), $this->request->getParam('password'))){
            // Пользователь успешно авторизован
            $this->view->isAuthorized = true;
            //$this->view->message = UI::$text['success'];
            /* Далее фронт-контроллер отобразит личную страницу пользоватлея,
             * поэтому запишем данные пользователя в объект вида
             */
            $user = $this->service->findUser('email', $this->request->getParam('email'));
            if ($user->isAdmin()) {
                $this->view->isAdmin = true;
            }
            $this->view->userName = $user->getName();
            $this->view->title = $user->getName();
            return $this->view;
        } else {
            $this->view->isAuthorized = false;
            $this->view->message = UI::$text['bad_login'];
            $this->view->title = 'войти';
            return $this->view;
        }
    }
    public function showMainPage() {
        $this->view->title = UI::$text['main_page'];
        return $this->service->isAuthorized($this->view);
    }
    public function showAccount() {
        $this->view->title = 'Личный кабинет';
        return $this->service->isAuthorized($this->view);
    }
    public function addUser(){
        if (!$this->service->isAuthorizedAdmin()){
            $this->view->message = UI::$text['acces_denied'];
            return $this->view;
        }
        // Создаем пользователя определенного типа (тип указан в запросе)
        $user = $this->service->createUserByType($this->request->getParam('type'));
        // Только у InternalUser есть пароль, установим его
        if ($user->getType() === User::TYPE_INTERNAL){
            $user->setPassword(md5(md5($this->request->getParam('password'))));
        }
        // Инициализируем пользователя (общие для всех свойства)
        $user->setName($this->request->getParam('name'));
        $user->setEmail($this->request->getParam('email'));
        $user->setRoleset($this->request->getParam('role'));
        if (true === $this->service->addUser($user)){
            // Пользователь создан, ему назначен id. Назначаем ему привилегии
            $privileges = new Privileges();
            $privileges->setId($user->getId());
            $privileges->setPrivilegesByRole($user->getRoleset());
            if (true !== $this->service->addUserPrivileges($privileges)){
                $this->view->message = UI::$text['error'];
                return $this->view;
            }
        }
        $this->view->message = '<a href="index.php?action=displayUser&id='.$user->getId().'">Пользователь</a> добавлен';
        return $this->view;
    }
    public function updateUser(){
        // Обновление данных пользователя (может лишь сам пользователь и админ)
        if (false === $user = $this->service->findUserById($this->request->getParam('id'))){
            $this->view->message = UI::$text['error'];
            return $this->view;
        }
        if (!$this->service->isAuthorizedUserId($this->request->getParam('id'))
            || !$this->service->isAuthorizedAdmin()){
            $this->view->message = UI::$text['acces_denied'];
            return $this->view;
        }
        // Только у InternalUser есть пароль, установим его
        if ($user->getType() == User::TYPE_INTERNAL){
            $user->setPassword(md5(md5($this->request->getParam('password'))));
        }
        // Инициализируем пользователя (общие для всех свойства)
        $user->setName($this->request->getParam('name'));
        $user->setEmail($this->request->getParam('email'));
        $this->view->message = $this->service->
            updateUser($user) ?  UI::$text['success'] :  UI::$text['error'];
        return $this->view;
    }
}