<?php
namespace surveyzilla\application\model\poll;
class Options
{
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    private $type;                          // тип выбора (radio, checkbox...)
    private $hasCustomField = false;        // будет ли индивидуальный вариант
    private $optionList = array();          // массив с вариантами (массив строк)
    public function __construct($type, $hasCustomField, array $optionList){
        $this->setType($type);
        $this->hasCustomField = (bool) $hasCustomField;
        $this->optionList = $optionList;
    }
    public function getType(){
        return $this->type;
    }
    public function setType($type){
        switch ($type){
            case self::TYPE_RADIO;
            case self::TYPE_CHECKBOX:
                $this->type = $type;
                break;
            default:
                throw new \LogicException('Invalid type of option list');
        }
    }
    public function getOptionList(){
        return $this->optionList;
    }
    /*public function setOptionList($list){
        $this->optionList = $list;
    }*/
    public function customFieldAllowed(){
        return ($this->hasCustomField) ? true : false;
    }
}