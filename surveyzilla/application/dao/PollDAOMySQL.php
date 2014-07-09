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
    public function addTempAnswer($token, $ans) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare('INSERT INTO `AnswerTemp`(`Token`, `AnswerObj`) '
                            . 'VALUES (?,?)');
        return $stmt->execute(array($token, $ans));
    }
    public function getTempAnswer($token) {
        $token = (float) $token;
        $dbh = DbConnection::getInstance()->getHandler();
        $sql = "SELECT `AnswerObj` FROM `AnswerTemp` "
             . "WHERE `Token` = '$token'";
        $stmt = $dbh->query($sql);
        $ans = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (empty($ans)) {
            throw new Exception('Cannot find TempAnswer');
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
    public function getNextItem($pollId, $itemId, array $options) {
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->prepare(
            'SELECT NextItemId FROM Logic '
          . 'WHERE PollId = :poll AND ItemId = :item AND OptionId = :opt'
        );
        $stmt->bindParam(':poll', $pollId, \PDO::PARAM_INT);
        $stmt->bindParam(':item', $itemId, \PDO::PARAM_INT);
        $stmt->bindParam(':opt', $options[0], \PDO::PARAM_INT);
        if (false == $stmt->execute()) {
            return;
        }
        $next = $stmt->fetch(\PDO::FETCH_NUM);
        // This was the las question, nowhere to go now.
        // Creating a specisl 'system' item to finish questioning a user
        if ($next[0] == 0) {
            $item = new Item();
            $item->isSystemFinal = true;
            return $item;
        }
        return $this->getItem($pollId, $next[0]);
    }
}