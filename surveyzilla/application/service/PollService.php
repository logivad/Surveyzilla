<?php
namespace surveyzilla\application\service;

use surveyzilla\application\dao\PollDAOMySQL;
use surveyzilla\application\model\poll\Answer;
use surveyzilla\application\view\UI;

class PollService
{
    private static $_instance;
    private $pollDAO;
    //private $userService;
    private function __construct(){
        /*empty*/
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
            self::$_instance->pollDAO = PollDAOMySQL::getInstance();
        }
        return self::$_instance;
    }
    
     /**
     * Creates temporary object Anser that lives while user is answering the poll.
     * Also set COOKIE for the token
     * @param type $pollId Id of the poll
     */
    public function createTempAnswer($pollId){
        $ans = new Answer($pollId);
        // Every poll should start from first item
        $ans->currentItem = 1;
        // For the case two ore more people answer at the same 
        // fraction of a second, let's do it in a cycle
        do {
            // Generating a token as a timestamp with microseconds
            $ans->generateToken();
            // If such a token already exists (answer will not be inserted and
            // $res will become 0 or FALSE), let's generate a new one
            $res = $this->pollDAO->addTempAnswer($ans);
        } while (empty($res));
        setcookie('token', $ans->token, time()+60*60*24*7);
        return $ans;
    }
    public function appendTempAnswer($token, array $options, $custopt) {
        $ans = $this->pollDAO->getTempAnswer($token);
        //echo 'old:';var_dump($ans);
        $ans->addItem($ans->currentItem, $custopt, $options);
        if (!$this->pollDAO->updateTempAnswer($ans)) {
            throw new Exception(UI::$text['error']);
        }
        //echo 'new:';var_dump($ans);
    }
    /**
     * Updates TempAnswer record
     * @param numeric $token
     * @param mixed $param Parameter to be updated
     * @param mixed $value Value for the parameter
     * @throws Exception
     */
    public function updateTempAnswer($token, $param, $value) {
        $ans = $this->pollDAO->getTempAnswer($token);
        if (!isset($ans->$param)) {
            throw new Exception('Wrong parameter!');
        }
        $ans->$param = $value;
        if (!$this->pollDAO->updateTempAnswer($ans)) {
            throw new Exception(UI::$text['error']);
        }
        return $ans;
    }
    /**
     * Returns Item object filled with data for a given item
     * @param type $pollId Poll Id
     * @param type $itemId Item Id 
     * @return obj Item object
     */
    public function getFirstItem($pollId) {
        return $this->pollDAO->getItem($pollId, 1);
    }
    public function getNextItem($token) {
        return $this->pollDAO->getNextItem($token);
    }
    public function getCurrentItem($token) {
        return $this->pollDAO->getCurrentItem($token);
    }
    public function isUniqueUser($pollId, $token){
        if (empty($token)) {
            return true;
        }
        $ans = $this->pollDAO->getTempAnswer($token);
        if ($ans->completed) {
            return false;
        }
    }
    /**
     * Ads TempAnswer data to the poll statistics
     * @param type $token
     */
    public function processTempAnswer($token) {
        $ans = $this->pollDAO->getTempAnswer($token);
        unset($ans->pollId, $ans->currentItem, $ans->items);
        $ans->completed = true;
        $this->pollDAO->updateTempAnswer($ans);
        setcookie('token', NULL, time() - 1000);
    }
    public function makeLogicArray(array $queryResult) {
        /*
         * $router[0] = current Item
         * $router[1] = options
         * $router[2] = next Item
         */
        $logic = array();
        foreach ($queryResult as $router) {
            if (!isset($logic[$router[0]])) {
                $logic[$router[0]] = array();
            }
            $logic[$router[0]][$router[1]] = $router[2];
        }
        /*
         * $logic[current Item] = array(options => next Item)
         */
        return $logic;
    }
}