<?php
namespace surveyzilla\application\model\poll;

class Answer
{
    // Id of the poll that is being answered
    public $pollId;
    // A token (current time) for answering the poll
    public $token;
    public $items=array();

    public function __construct($pollId){
        $this->pollId = $pollId;
    }
    public function generateToken(){
        return $this->token = microtime(true);
    }
    public function addItem($itemId, $custom, array $options){
        $this->items[$itemId] = array('custopt' => $custom, 'opts' => $options);
    }
}