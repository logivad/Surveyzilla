<?php
namespace surveyzilla\application\model\poll;
class Poll
{
    private $id;                // идентификатор опроса
    private $name;                // название опроса
    private $userId;            // Id пользователя, создавшего данный опрос
    private $items = array();    // массив с элементами опроса (объекты класса Item)

    public function __construct($creatorId, $name){
        $this->creatorId = $creatorId;
        $this->name = $name;
    }
    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function getName(){
        return $this->name;
    }
    public function setName($name){
        if ($this->isValidName($name))
            $this->name = $name;
    }
    private function isValidName($name){
        return strlen($name) > 1;
    }
    public function getCreatorId(){
        return $this->creatorId;
    }
    public function getItem($id){
        return $this->items[$id];
    }
    public function getItemsArr(){
        return $this->items;
    }
    public function addItem(Item $item){
        $this->items[] = $item;
    }
    public function deleteItem($id){
        unset($this->item[$id]);
    }
    public function getSize(){
        return count($this->items);
    }
}