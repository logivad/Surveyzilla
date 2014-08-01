<?php
namespace surveyzilla\application\controller;

use LogicException;
use stdClass;
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
     * @return object Returns View object for rendering the page
     * @throws LogicException
     */
    public function runPoll() {
        // At leat pollId must be set (ids start from "1")
        $pollId = $this->request->get('poll');
        if (empty($pollId)){
            var_dump($pollId);
            $this->view->item = new stdClass();
            $this->view->pollName = UI::$lang['error'];
            return $this->view->setMessage(UI::$lang['poll_notfound']);
        }
        // Token is a timestamp and looks like this: 1404984161.9609
        $token = filter_input(INPUT_COOKIE, "poll$pollId", FILTER_VALIDATE_FLOAT);
        // Quizze can answer the poll just once
        if (false === $this->pollService->isUniqueUser($pollId, $token)){
            $this->view->item = new stdClass();
            $this->view->pollName = UI::$lang['error'];
            return $this->view->setMessage(UI::$lang['poll_answered']);
        }
        // If no token is given, a new quizzee has come and he needs a token
        if (empty($token)){
            // Get the first poll item if it exists
            $item = $this->pollService->getFirstItem($pollId);
            if (empty($item)) {
                $this->view->item = new stdClass();
                $this->view->item->pollName = UI::$lang['error'];
                return $this->view->setMessage(UI::$lang['poll_notfound']);
            }
            // Create a record for temp answer and set a cookie for a token
            $this->pollService->createTempAnswer($pollId);
            // Display the item for user to answer it
            $this->view->item = $item;
            $this->view->pollName = $item->pollName;
            return $this->view;
        } else {
            // Quizzee came again, let's check if he/she answered something
            if (!$this->request->isSetParam('submit')) {
                // If no answer provided, show the user an Item to be answered.
                // So a user can continue poll after closing the browser
                $item = $this->pollService->getCurrentItem($token);
                if (empty($item)) {
                    $this->view->item = new stdClass();
                    //$this->view->item->pollName = '';
                    return $this->view->setMessage(
                        UI::$lang['error'].' '.UI::$lang['poll_notfound']
                    );
                }
                $this->view->item = $item;
                $this->view->pollName = $item->pollName;
                return $this->view;
            }
            // So, a user has token and clicked submit button. If there is
            // an item parameter in REQUEST, let's check if this is a proper id.
            $item = $this->pollService->getCurrentItem($token);
            $itemForm = $this->request->get('item');
            if ($itemForm && $item->id != $itemForm) {
                // Quizzee is trying to answer the wrong question!
                return $this->view->setMessage(
                    UI::$lang['error'] . '<p>' . UI::$lang['wrong_poll'] .
                    '</p><p><a href="index.php?a=run&poll=' . $item->pollId
                    . '">' . UI::$lang['back'] . '</a></p>'
                );
            }
            // Let's check if there is something in opts
            if (null == $this->request->get('opts')) {
                // No options selected. It's allowed for "checkbox", but
                // permissible for "radio"
                if ($item->inputType === 'checkbox') {
                    // Imitating such selection that default next Item fires
                    $this->request->set('opts', array(-1));
                } elseif ($item->inputType === 'radio') {
                    $this->view->item = $item;
                    $this->view->pollName = $item->pollName;
                    $msg = UI::$lang['none_selected'] . '<br>'
                         . '<p><a href="index.php?a=run&poll=' . $item->pollId
                         . '">' . UI::$lang['back'] . '</a></p>';
                    return $this->view->setMessage($msg);
                }
            }
            // Quizzee has an answer, let's save it to TempAnswer
            // This function also updates $currentItem
            $this->pollService->appendTempAnswer(
                $token,
                $this->request->get('opts'),
                $this->request->get('custopt'),
                $item->inStat
            );
            // Gettting next answer according to the poll logic
            $item = $this->pollService->getNextItem($token);
            if (empty($item)) {
                $this->view->item = new stdClass();
                return $this->view->setMessage(UI::$lang['error']);
            }
            // When poll is finished and no Final Item is provided
            // (Logic nextItem == 0), DAO returns a special 'system' Item with
            // isSystemFinal set to TRUE
            if ($item->isSystemFinal) {
                $this->view->item = $item;
                $this->pollService->processTempAnswer($token);
                if ($item->pollShowStat) {
                    // Just set "stat" property to anything but false
                    $this->view->stat = true;
                    $this->view->pollId = $pollId;
                    return $this->view;
                }
                return $this->view->setMessage(UI::$lang['poll_end']);
            }
            if ($item->isFinal) {
                // The last item appeared, which means the poll is finished.
                // TempAnswer must be added to poll statistics and then deleted
                $this->pollService->processTempAnswer($token);
                $this->view->pollName = $item->pollName;
                $this->view->item = $item;
                return $this->view;
            }
            // Now we need to set $ans->currentItem to be equal to the id of the 
            // Item being displayed, so that the answer counts for this Item
            $this->pollService->updateTempAnswer($token, 'currentItem', $item->id);
            $this->view->item = $item;
            $this->view->pollName = $item->pollName;
            return $this->view;
        }
    }
    /**
     * Reads stat object from cache file and saves it in "stat" 
     * property of the $view object
     * 
     * @return object
     */
    public function getStat() {
        if (!$this->request->isSetParam('poll')) {
            return $this->view->setMessage(UI::$lang['poll_notfound']);
        }
        $this->view = $this->pollService->getStat($this->request->get('poll'), $this->view);
        if (!isset($this->view->stat)) {
            return $this->view->setMessage(UI::$lang['poll_notfound']);
        }
        $this->view->pollId = $this->request->get('poll');
        return $this->view;
    }
}