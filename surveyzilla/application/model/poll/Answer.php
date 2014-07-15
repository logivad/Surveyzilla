<?php
namespace surveyzilla\application\model\poll;

class Answer
{
    // Id of the poll that is being answered
    public $pollId;
    // The Item waiting for an answer
    public $currentItem;
    // A token (current time) for answering the poll
    public $token;
    // Is TRUE when thist answer has been used up
    public $completed;
    public $items=array();

    public function __construct($pollId){
        $this->pollId = $pollId;
    }
    public function generateToken(){
        return $this->token = microtime(true);
    }
    /**
     * Saves a user's answer to the $items array
     * @param int $itemId ID of the item in a database
     * @param string $custom User's custom option
     * @param bool $inStat Whether this answer is shown in statistics
     * @param array $options Selected options from the web form
     */
    public function addItem($itemId, $custom=null, array $options=array(), $inStat=true){
        /*
         * Logic uses a bitmask of options (`Options` INT UNSIGNED, 4 bytes).
         * That means If a quizzee selects options 1, 2 and 4, Logic `Options`
         * must contain binary number 1011 e.i. decimal 11:
         *  options    4 3 2 1
         *             1 0 1 1 = 2^0 + 2^1 + 2^3 = 1 + 2 + 8 = 11
         * Let's convert!
         */
        $bitMask = 0;
        foreach ($options as $val) {
            $bitMask += pow(2, $val-1);
        }
        $this->items[$itemId] = array(
            'custopt' => $custom,
            'opts' => (int) $bitMask,
            'optsArr' => $options,
            'stat' => ($inStat) ? true : false
        );
    }
    public function getCurrentOpts() {
        return $this->items[$this->currentItem]['opts'];
    }
}