<?php
namespace surveyzilla\application\model\poll;
class Item
{
    private $id;
    // текст вопроса
    private $question;
    // варианты ответа
    private $options;

    public function __construct($question){
        $this->setQuestion($question);
    }
    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function getQuestion(){
        return $this->question;
    }
    public function setQuestion($q){
        if (strlen($q) > 2){
            $this->question = $q;
        }
    }
    public function setOptions(Options $options){
        $this->options = $options;
    }
    public function getOptions(){
        return $this->options;
    }
    public function toCSV(){
        $strItem = '"'.$this->question.'",';
        $strOptions = '"'.$this->options->getType().'","'.$this->options->CustomFieldAllowed().'","';
        $strOptionsArr = implode('","', $this->options->getOptionList());
        return $strItem.$strOptions.$strOptionsArr.'"';
    }
}