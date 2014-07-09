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
     * Creates temporary object Anser that lives while user is answering the poll
     * @param type $pollId Id of the poll
     */
    public function createTempAnswer($pollId){
        $ans = new Answer($pollId);
        // For the case two ore more people answer at the same 
        // fraction of a second, let's do it in a cycle
        do {
            // Generating a token as a timestamp with microseconds
            $token = $ans->generateToken();
            $ans = serialize($ans);
            // If such a token already exists (answer will not be inserted and
            // $res will become 0 or FALSE), let's generate a new one
            $res = $this->pollDAO->addTempAnswer($token, $ans);
        } while (empty($res));
        return $token;
    }
    public function appendTempAnswer($token, $item, array $options, $custopt) {
        $ans = $this->pollDAO->getTempAnswer($token);
        $ans->addItem($item, $custopt, $options);
        if (!$this->pollDAO->updateTempAnswer($ans)) {
            throw new Exception(UI::$text['error']);
        }
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
    public function getNextItem($pollId, $itemId, array $options) {
        return $this->pollDAO->getNextItem($pollId, $itemId, $options);
    }
    public function isUniqueUser($pollId, $token){
        return true;
    }
}