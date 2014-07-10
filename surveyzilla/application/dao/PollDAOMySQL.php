<?php
namespace surveyzilla\application\dao;

use surveyzilla\application\model\poll\Item;
use surveyzilla\application\model\poll\Poll;
use surveyzilla\application\dao\DbConnection;
use surveyzilla\application\model\poll\Answer;

class PollDAOMySQL implements IPollDAO
{
    private static $_instance;
    private function __construct(){
        /*empty*/
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function addItem($pollId, Item $item) {
        
    }

    public function addPoll(Poll $poll) {
        
    }

    public function deletePoll($id) {
        
    }
    public function addTempAnswer(Answer $ans) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            'INSERT INTO `AnswerTemp`(`Token`, `AnswerObj`) VALUES (?,?)'
        );
        return $stmt->execute(array($ans->token, serialize($ans)));
    }
    public function getTempAnswer($token) {
        $dbh = DbConnection::getInstance()->getHandler();
        $sql = "SELECT `AnswerObj` FROM `AnswerTemp` "
             . "WHERE `Token` = '$token'";
        $stmt = $dbh->query($sql);
        $ans = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (empty($ans)) {
            return;
        }
        return unserialize($ans['AnswerObj']);
    }
    public function updateTempAnswer(Answer $ans) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare('UPDATE AnswerTemp SET AnswerObj = ? '
                            . 'WHERE Token = ?');
        return $stmt->execute(array(serialize($ans), $ans->token));
    }
    /**
     * Returns Item object filled with data for a given item
     * @param type $pollId Poll Id
     * @param type $itemId Item Id 
     * @return obj Item object
     */
    public function getItem($pollId, $itemId) {
        // Getting Item from DB (without options)
        $sql = "SELECT pi.id, pi.pollId, pi.questionText, pi.imagePath,"
             . "pi.inputType, pi.isFinal, pi.finalLink, pi.finalComment, "
             . "p.Name AS pollName "
             . "FROM PollItems AS pi INNER JOIN Polls AS p "
             . "ON pi.id = '$itemId' "
             . "AND pi.pollId = '$pollId' "
             . "AND pi.pollId = p.Id";
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->query($sql);
        $item = $stmt->fetchObject('surveyzilla\application\model\poll\Item');
        if (empty($item)) {
            return;
        }
        // Getting options for the Item
        $options = array();
        $sql = "SELECT `Id`, `OptionText` FROM `ItemOptions` "
             . "WHERE `PollId` = '$pollId' AND `ItemId` = '$itemId'";
        foreach ($dbh->query($sql) as $option) {
            $options[$option['Id']] = $option['OptionText'];
        }
        $item->options = $options;
        return $item;
    }
    /**
     * 
     * @param numeric $token
     * @return obj Item 
     */
    public function getNextItem($token) {
        // Let's decide what should be next item according to the answers
        $ans = $this->getTempAnswer($token);
        if (empty($ans->items)) {
            // If no answer yet, just display the first one
            return $this->getItem($ans->pollId, 1);
        }
        // Using Logic to find next Item
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            'SELECT NextItemId FROM Logic '
          . "WHERE PollId = {$ans->pollId} "
          . "AND ItemId = {$ans->currentItem} "
          . "AND Options = {$ans->getCurrentOpts()}"
        );
        if (false == $stmt->execute()) {
            return;
        }
        $next = $stmt->fetch(\PDO::FETCH_NUM);
        if ($next[0] == 0) {
            // This was the las question, nowhere to go now.
            // Creating a specisl 'system' item to finish questioning a user
            $item = new Item();
            $item->isSystemFinal = true;
            return $item;
        }
        return $this->getItem($ans->pollId, $next[0]);
    }
}