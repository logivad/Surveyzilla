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
    public function runPoll() {
        // At leat pollId must be set (ids start from "1")
        $pollId = $this->request->get('poll');
        if (empty($pollId)){
            return $this->view->setMessage(UI::$text['poll_notfound']);
        }
        // Quizze can answer the poll just once
        if (!$this->pollService->isUniqueUser($pollId, $this->request->get('token'))){
            return $this->view->setMessage(UI::$text['poll_answered']);
        }
        // Token is a timestamp and looks like this: 1404984161.9609
        $token = filter_input(INPUT_COOKIE, 'token', FILTER_VALIDATE_FLOAT);
        // If no token is given, a new quizzee has come and he needs a token
        if (empty($token)){
            // Get the first poll item if it exists
            $item = $this->pollService->getFirstItem($pollId);
            if (empty($item)) {
                return $this->view->setMessage(UI::$text['poll_notfound']);
            }
            // Create a record for temp answer and set a cookie for a token
            $this->pollService->createTempAnswer($pollId);
            // Display the item for user to answer it
            $this->view->item = $item;
            return $this->view;
        } else {
            // Quizzee came again, let's check if he/she answered something
            if (!$this->request->isSetParam('opts') && !$this->request->isSetParam('custopt') ) {
                // If no answer provided, show the user an Item to be answered.
                // So a user can continue poll after closing the browser
                $item = $this->pollService->getNextItem($token);
                if (empty($item)) {
                    return $this->view->setMessage(UI::$text['error']);
                }
                $this->view->item = $item;
                return $this->view;
            }
            // Quizzee has an answer, let's save it to TempAnswer
            // This function also updates $currentItem
            $this->pollService->appendTempAnswer(
                $token,
                $this->request->get('opts'),
                $this->request->get('custopt')
            );
            // Gettting next answer according to the poll logic
            $item = $this->pollService->getNextItem($token);
            if (empty($item)) {
                return $this->view->setMessage(UI::$text['error']);
            }
            // When poll is finished and no Final Item is provided
            // (Logic nextItem == 0), DAO returns a special 'system' Item with
            // isSystemFinal set to TRUE
            if ($item->isSystemFinal) {
                return $this->view->setMessage(UI::$text['poll_end']);
            }
            if ($item->isFinal) {
                // The last item appeared, which means the poll is finished.
                // TempAnswer must be added to poll statistics and then deleted
                $this->pollService->processTempAnswer($token);
            }
            // Now we need to set $ans->currentItem to be equal to the id of the 
            // Item being displayed, so that the answer counts for this Item
            //$this->pollService->updateTempAnswer($token, 'currentItem', $item->id);
            $this->view->item = $item;
            return $this->view;
        }
    }
}