<?php
namespace surveyzilla\application\model\poll;
class Answer
{
    private $pollId;            // опрос, для которого предназначен данный ответ
    private $token;                // талон на прохождение опроса
    private $items=array();        // $items[itemId] = [customOption, option 1, option 2, ... option N]

    public function __construct($pollId){
        $this -> pollId = $pollId;
    }
    public function getPollId(){
        return $this -> pollId;
    }
    public function getToken(){
        return $this -> token;
    }
    public function setToken($token){
        $this->token = $token;
    }
    public function generateToken(){
        $this -> token = md5(microtime().rand(100,999));
    }
    public function setItems(array $items){
        $this->items = $items;
    }
    public function getItems(){
        return $this->items;
    }
    public function addItem($itemId, $custom, array $items){
        array_unshift($items, $custom);
        $this->items[$itemId] = $items;
    }
}