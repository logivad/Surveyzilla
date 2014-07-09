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
        if (empty($_COOKIE['token'])){
            $token = $this->pollService->createTempAnswer($pollId);
            setcookie('token', $token, time()+60*60*24*7);
            // Quizzee is waiting, let's display the very first poll item!
            $item = $this->pollService->getFirstItem($pollId);
            if (empty($item)) {
                $this->view->message = UI::$text['poll_notfound'];
                return $this->view;
            }
            $this->view->item = $item;
            return $this->view;
        } else {
            if (empty($this->request->get('item')) 
                || sizeof($this->request->get('opts')) < 1){
                $this->view->message = UI::$text['error'];
                return $this->view;
            }
            // Quizzee answered something, let's save it to TempAnswer
            $this->pollService->appendTempAnswer(
                $_COOKIE['token'],
                $this->request->get('item'),
                $this->request->get('opts'),
                $this->request->get('custopt')
            );
            // Gettting next answer according to the poll logic
            $item = $this->pollService->getNextItem(
                $pollId,
                $this->request->get('item'),
                $this->request->get('opts')
            );
            if (empty($item)) {
                $this->view->message = UI::$text['poll_notfound'];
                return $this->view;
            }
            // When poll is finished (nextItem == 0), DAO returns this Item
            if ($item->isSystemFinal) {
                $this->view->message = UI::$text['poll_end'];
                return $this->view;
            }
            $this->view->item = $item;
            return $this->view;
        }
    }
}