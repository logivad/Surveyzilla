<?php
namespace surveyzilla\application\service;

use Exception;
use RuntimeException;
use surveyzilla\application\dao\PollDAO;
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
            self::$_instance->pollDAO = PollDAO::getInstance();
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
            $res = $this->pollDAO->saveTempAnswer($ans);
        } while (empty($res));
        setcookie("poll$pollId", $ans->token, time()+60*60*24*7);
        return $ans;
    }
    public function appendTempAnswer($token, array $options, $custopt, $inStat) {
        $ans = $this->pollDAO->getTempAnswer($token);
        $ans->addItem($ans->currentItem, $custopt, $options, $inStat);
        if (!$this->pollDAO->saveTempAnswer($ans)) {
            throw new Exception(UI::$lang['error']);
        }
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
        if (!$this->pollDAO->saveTempAnswer($ans)) {
            throw new Exception(UI::$lang['error']);
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
        $this->pollDAO->deleteTempAnswer($token);
        setcookie('poll'.$ans->pollId, NULL, time() - 10000);
        if (!$this->pollDAO->processTempAnswer($ans)) {
            throw new RuntimeException('Error processing the answer');
        }
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
      /**
     * Populate View with an array filled by statistical data about the poll.
     * Merges results for equal questions.
     * @param int $pollId ID of the poll which statistics to get
     * @return object $view Returns an array filled with stat. data
     */
    public function getStat($pollId, $view) {
        /**
         * Finds IDs of the first and last Items
         */
        function getExtremeItems($arr) {
            $keysArr = array_keys($arr);
            sort($keysArr);
            return array('first' => array_shift($keysArr), 'last' => array_pop($keysArr));
        }
        $view->pollId = $pollId;
        $rawStat = $this->pollDAO->getPollAnswers($pollId);
        // How many people has voted?
        $votesTotal = $this->pollDAO->getPollVotesCount($pollId);
        // Options is a list of comma separated numbers, let's make an array    
        for ($i = 0, $len = sizeof($rawStat); $i < $len; $i++) {
            $rawStat[$i]['Options'] = explode(',', $rawStat[$i]['Options']);
        }
        // Calculate number of votes for every option
        $stat = array();
        foreach ($rawStat as $item) {
            if (!isset($stat[$item['ItemId']])) {
                $stat[$item['ItemId']] = array();
            }
            // Counting options for this item
            foreach ($item['Options'] as $opts) {
                if (!isset($stat[$item['ItemId']][$opts])) {
                    $stat[$item['ItemId']][$opts] = 1;
                } else {
                    ++$stat[$item['ItemId']][$opts];
                }
            }
        }
        // Now $stat array looks like this:
        // 
        //    array (size=5)            // $stat array
        //      1 =>                    // For Item #1...
        //        array (size=2)        // someone selected
        //          1 => int 1          // option #1 once
        //          3 => int 2          // option #3 twice
        //      3 =>                    // For Item #3
        //        array (size=1)
        //          1 => int 1          // option #1 once

        // Array of questions (corresponds to item ID's) excluding those
        // not to be used for statistics (inStat = false)
        $questions = $this->pollDAO->getItemQuestions($pollId);
        // Array with optin text for each item/option
        $optionText= $this->pollDAO->getOptions($pollId);
        // Forming final array were ID's are replaced with texts.
        // Also unwanted elements are deleted (inStat = false)
        $final = array();
        // Get min/max ID's among items
        $extremeItems = getExtremeItems($questions);
        //echo "<b><small>Total votes: $votesTotal</small></b><br>"; var_dump($stat); var_dump($questions); var_dump($optionText);
        // Sabstitute item and poll id's with respective titles.
        // If some items are not present in $stat array, that means nobody answered
        // them yet and we'll just need to show them with 0 votes
        for ($itemId = $extremeItems['first']; $itemId <= $extremeItems['last']; $itemId++) {
            // If the Item does not exist in $stat, it is not yet answered
            if (!array_key_exists($itemId, $stat) && array_key_exists($itemId, $optionText)) {
                //var_dump($itemId);
                $stat[$itemId] = array();
            }
            if (!array_key_exists($itemId, $optionText) || !isset($questions[$itemId])) {
                // Such ID doesn not exist in $optionText array
                // (it is final Item or some other reason) or is not used in statistics
                continue;
            }
            foreach ($optionText[$itemId] as $key => $val) {
                if ($key == 0) {
                    continue;
                }
                // Just set 0 (no answers so far)
                if (!array_key_exists($key, $stat[$itemId])) {
                    $stat[$itemId][$key] = 0;
                }
            }
            $options = $stat[$itemId];
            // Can be already set (poll can containt items with the same questions)
            if (!isset($final[$questions[$itemId]])) {
                $final[$questions[$itemId]] = array();
            }
            foreach ($options as $optionId => $voteCount) {
                // Show votes as total number and percentage
                if (!isset($final[$questions[$itemId]][$optionText[$itemId][$optionId]])) {
                    $final[$questions[$itemId]][$optionText[$itemId][$optionId]] = 
                        array(
                            'total' => $voteCount, 
                            'percent' => round($voteCount * 100 / $votesTotal, 2)
                        );
                } else {
                    // Such question already exists! Let's merge results
                    $final[$questions[$itemId]][$optionText[$itemId][$optionId]]['total'] += $voteCount;
                    $final[$questions[$itemId]][$optionText[$itemId][$optionId]]['percent'] = round(
                        $final[$questions[$itemId]][$optionText[$itemId][$optionId]]['total'] * 100 / $votesTotal, 2
                    );
                }
            }
        }
        //var_dump($final); exit;
        $view->stat = $final;
        $view->pollName = $this->pollDAO->getPollName($pollId);
        return $view;
    }
}