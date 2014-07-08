<?php
namespace surveyzilla\application\controller;

use LogicException;
use surveyzilla\application\model\poll\Item;
use surveyzilla\application\model\poll\Logic;
use surveyzilla\application\model\poll\Options;
use surveyzilla\application\model\Request;
use surveyzilla\application\model\View;
use surveyzilla\application\service\PollService;
use surveyzilla\application\service\UserService;
use surveyzilla\application\view\UI;

class PollController
{
    private $pollService;
    private $userService;
    private $request;
    private $view;
    private static $_instance;
    private function __construct(){
        $this->view = new View();
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
            self::$_instance->userService = UserService::getInstance();
            self::$_instance->pollService = PollService::getInstance();
        }
        return self::$_instance;
    }
    public function setPollService($pollService){
        $this->pollService = $pollService;
    }
    public function addPoll(){
        // Добавить опрос может лишь авторизованный пользователь
        if (false === $user = $this->userService->isAuthorized()){
            $this->view->message = UI::$text['acces_denied'];
            return $this->view->message;
        }
        if (false === $privileges = $this->userService->findUserPrivilegesById($user->getId())){
            throw new LogicException('Cannot add Poll. User privileges not found');
        }
        if (false === $privileges->canCreatePoll()){
            $this->view->message = UI::$text['limit_poll'];
            return $this->view->message;
        }
        $id = $this->pollService->addPoll($user, $privileges, $this->request->getParam('name'));
        if ($id < 0){
            $this->view->mesage = UI::$text['error']."($id)";
        } else {
            $this->view->message = UI::$text['success'].'! <a href="index.php?action=displayPoll&id='.$id.'">'.UI::$text['view_poll'].'</a>';
        }
        return $this->view;
    }
    public function addItem(){
        $item = new Item($this->request->getParam('question'));
        $opt = new Options($this->request->getParam('optionsType'), $this->request->getParam('hasCustomField'), $this->request->getParam('optionsArr'));
        $item->setOptions($opt);
        if (true === $this->pollService->addItem($this->request->getParam('pollId'), $item)){
            $this->view->message = 'Вопрос добавлен';
        } else {
            $this->view->message = UI::$text['error'];
        }
        return $this->view;
    }
    public function displayPoll(){
        $this->view->poll = $this->pollService->findPollById($this->request->getParam('id'));
        return $this->view;
    }
    public function setRequest(Request $request){
        $this->request = $request;
    }
    /**
     * Runs the poll
     * 
     * @return obj Returns View object for rendering the page
     * @throws LogicException
     */
    public function runPoll(){
        // At leat pollId must be set
        $pollId = $this->request->get('poll');
        if (empty($pollId) && $pollId !== 0){
            $this->request->message = 'Cannot run poll! Poll id not set';
            return $this->request;
        }
        if (!$this->pollService->isUniqueUser($pollId, $this->request->get('token'))){
            $this->view->message = UI::$text['poll_answered'];
            return $this->view;
        }
        // If no token is given, a new quizzee has come and he needs a token
        if (!$this->request->isSetParam('token')){
            // новый опрашиваемый (еще не имеет талона)
            $token = $this->pollService->addAnswer($pollId);
            setcookie('token', $token, time()+60*60*24*7);
            // Отображаем первый Item опроса, пусть пользователь начинает отвечать
            $poll = $this->pollService->findPollById($pollId);
            if (empty($poll)){
                throw new LogicException('Cannot run, poll not found!');
            }
            $this->view->item = $poll->getItem(0);
            $this->view->pollId = $pollId;
            $view = $this->view;
            require_once 'surveyzilla/application/view/header.php';
            require_once 'surveyzilla/application/view/displayItem.php';
        } else {
            if (empty($this->request->get('itemId')) && $this->request->get('itemId') !== 0
                || sizeof($this->request->get('options')) < 1){
                throw new LogicException('Cannot process answer, item or option id is not set');
            }
            $ans = $this->pollService->findAnswer($_COOKIE['token']);
            if ($ans->getPollId() != $pollId){
                throw new LogicException('This answer file does not belong to the poll');
            }
            // записываем ответ пользователя
            $ans->addItem($this->request->get('itemId'), $this->request->get('customOption'), $this->request->get('options'));
            $this->pollService->updateAnswer($ans);
            // задаем пользователю следующий вопрос (согласно логике)
            $lg = $this->pollService->findLogic($pollId);
            $nextItem = $lg->getNextItem($this->request->get('itemId'),$this->request->get('options'));
            if ($nextItem === Logic::END){
                // Заносим талон в список использованных и считаем результаты
                $this->pollService->addUsedToken($pollId, $this->request->get('token'));
                $this->pollService->appendResults($ans);
                // Выводим сообщение об окончании опроса
                $this->view->message = UI::$text['poll_end'];
                $view = $this->view;
                require_once 'surveyzilla/application/view/header.php';
                require_once 'surveyzilla/application/view/message.php';
                exit();
            } else {
                $poll = $this->pollService->findPollById($pollId);
                if ($poll === false){
                    throw new LogicException('Poll not found');
                }
                $this->view->item = $poll->getItem($nextItem);
                $this->view->pollId = $pollId;
                $view = $this->view;
                require_once 'surveyzilla/application/view/header.php';
                require_once 'surveyzilla/application/view/displayItem.php';
            }
        }
    }
}