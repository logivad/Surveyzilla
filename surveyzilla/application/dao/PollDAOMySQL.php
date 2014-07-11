<?php
namespace surveyzilla\application\dao;

use Exception;
use PDO;
use surveyzilla\application\dao\DbConnection;
use surveyzilla\application\model\poll\Answer;
use surveyzilla\application\model\poll\Item;
use surveyzilla\application\model\poll\Poll;
use surveyzilla\application\service\PollService;

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
        $ans = $stmt->fetch(PDO::FETCH_ASSOC);
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
    public function deleteTempAnswer($token) {
        $dbh = DbConnection::getInstance()->getHandler();
        $dbh->exec("DELETE FROM AnswerTemp WHERE Token = $token");
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
        $sql = "SELECT `OptionText` FROM `ItemOptions` "
             . "WHERE `PollId` = '$pollId' AND `ItemId` = '$itemId' "
             . "ORDER BY `PollId`";
        $stmt = $dbh->query($sql);
        $resultArr = $stmt->fetchAll(PDO::FETCH_NUM);
        foreach ($resultArr as $key => $option) {
            $options[$key + 1] = $option[0];
        }
        $item->options = $options;
        return $item;
    }
    public function getCurrentItem($token) {
        $ans = $this->getTempAnswer($token);
        if (!isset($ans->currentItem)) {
            throw new Exception('$currentItem not set!');
        }
        return $this->getItem($ans->pollId, $ans->currentItem);
    }
    /**
     * 
     * @param numeric $token
     * @return obj Item 
     */
    public function getNextItem($token) {
        // Let's decide what should be next item according to the answers
        $ans = $this->getTempAnswer($token);
        // Take the part of the Logic table for current pollId and itemId
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            'SELECT ItemId, Options, NextItemId FROM Logic '
          . "WHERE PollId = {$ans->pollId} "
          . "AND ItemId = {$ans->currentItem}"
        );
        if (false == $stmt->execute()) {
            return;
        }
        $res = $stmt->fetchAll(PDO::FETCH_NUM);
        // Make a convenient array from the result
        $logic = PollService::getInstance()->makeLogicArray($res);
        if (isset($logic[$ans->currentItem][$ans->getCurrentOpts()])) {
            $nextItemId = $logic[$ans->currentItem][$ans->getCurrentOpts()];
        } elseif (isset($logic[$ans->currentItem][0])) {
            // The last chance - default next Item
            $nextItemId = $logic[$ans->currentItem][0];
        }
        if (!isset($nextItemId)) {
            throw new Exception('Next Item id is not defined');
        }
        
        if ($nextItemId == 0) {
            // This was the las question, nowhere to go now.
            // Creating a specisl 'system' item to finish questioning a user
            $item = new Item();
            $item->isSystemFinal = true;
            return $item;
        }
        return $this->getItem($ans->pollId, $nextItemId);
    }
}