<?php
namespace surveyzilla\application\dao;

use Exception;
use PDO;
use surveyzilla\application\dao\DbConnection;
use surveyzilla\application\model\poll\Answer;
use surveyzilla\application\model\poll\Item;
use surveyzilla\application\model\poll\Poll;
use surveyzilla\application\service\PollService;

class PollDAO implements IPollDAO
{
    private static $_instance;
    // Directory for temp answer files
    private $tempAnsDir = 'temp/';
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
    /**
     * Inserts answer data to a database (appends it to the `Answers` table)
     * @param \surveyzilla\application\model\poll\Answer $ans Answer object
     * @return boolean Returns TRUE on success or FALSE on failure
     */
    public function processTempAnswer(Answer $ans) {
        $dbh = DbConnection::getInstance()->getHandler();
        $sql = '';
        foreach ($ans->items as $itemId => $val) {
            $options = implode(',', $val['optsArr']);
            $custom = ($val['custopt']) ? $val['custopt'] : '';
            $sql .= "INSERT INTO Answers (PollId, Token, ItemId, Options, CustomText) "
                 . "VALUES ({$ans->pollId},{$ans->token} , $itemId, '$options', '$custom');";
        }
        $stmt = $dbh->prepare($sql);
        if (false == $stmt->execute()) {
            return false;
        }
        return true;
    }
    public function saveTempAnswer(Answer $ans) {
        return file_put_contents($this->tempAnsDir . $ans->token, serialize($ans));
    }
    public function getTempAnswer($token) {
        return unserialize(file_get_contents($this->tempAnsDir . $token));
    }
    public function deleteTempAnswer($token) {
        if (file_exists($this->tempAnsDir . $token)) {
            unlink($this->tempAnsDir . $token);
        }
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
             . "pi.inputType, pi.inStat, pi.isFinal, pi.finalLink, pi.finalComment, "
             . "p.ShowStat AS pollShowStat, p.Name AS pollName "
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
             . "ORDER BY `Id`";
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
            // Let's find out whethe statistics should be displayed
            $stmt = $dbh->prepare(
                "SELECT ShowStat, Id FROM Polls WHERE Id = {$ans->pollId}"
            );
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res['ShowStat']) {
                $item->pollShowStat = true;
            }
            $item->pollId = $res['Id'];
            return $item;
        }
        return $this->getItem($ans->pollId, $nextItemId);
    }
    public function getPollAnswers($pollId) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            "SELECT ItemId, Options FROM Answers WHERE PollId = $pollId"
        );
        if (false == $stmt->execute()) {
            throw new Exception('Cannot get statistics!');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPollVotesCount($pollId) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            "SELECT COUNT(Token) as votes FROM "
          . "(SELECT Token FROM `Answers` WHERE PollId = $pollId GROUP BY Token) as t"
        );
        if (false == $stmt->execute()) {
            throw new Exception('Cannot get votes count!');
        }
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['votes'];
    }
    public function getItemQuestions($pollId) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            "SELECT id, questionText FROM PollItems "
          . "WHERE pollId = $pollId AND NOT questionText IS NULL "
          . "AND inStat = TRUE"
        );
        if (false == $stmt->execute()) {
            throw new Exception('Cannot get item questions!');
        }
        $res = array();
        while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $res[$item['id']] = $item['questionText'];
        }
        return $res;
    }
    public function getOptions($pollId) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            "SELECT ItemId, OptionText FROM ItemOptions "
          . "WHERE PollId = $pollId ORDER BY Id"
        );
        if (false == $stmt->execute()) {
            throw new Exception('Cannot get item options!');
        }
        $res = array();
        while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($res[$item['ItemId']])) {
                // first element is null so options kind of start from "1"
                $res[$item['ItemId']] = array(null);
            }
            $res[$item['ItemId']][] = $item['OptionText'];
        }
        return $res;
    }
}