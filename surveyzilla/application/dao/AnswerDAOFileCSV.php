<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\poll\Answer;
class AnswerDAOFileCSV implements IAnswerDAO
{
    private $path;
    private static $_instance;
    private function __construct(){
        /*пусто*/
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function setPath($path){
        $this->path = $path;
    }
    public function addAnswer($pollId){
        /*    Файл ответа в первой строке содержит id соответствующего опроса.
            Последующие строки содержат данные (в CSV-формате):
            "Item id","Custom field","Option id","Option id","Option id", ...
        */
        if (!isset($this->path)){
            throw new \LogicException('Cannot add an Answer, path to CSV file is not set');
        }
        $ans = new Answer($pollId);
        $ans->generateToken();
        while (file_exists($this->path.'ans_'.$ans->getToken().'.csv')){
            // Обеспечиваем уникальность талона
            $ans->generateToken();
        }
        // Создаем пустой файл с именем по значению талона
        if (false === file_put_contents($this->path.'ans_'.$ans->getToken().'.csv', $pollId.PHP_EOL)){
            throw new \LogicException('Error writing to file '.$this->path.'ans_'.$ans->getToken().'.csv');
        }
        return $ans->getToken();
    }
    public function updateAnswer(Answer $ans){
        if (!isset($this->path)){
            throw new \LogicException('Cannot update an Answer, path to CSV file is not set');
        }
        if (!file_exists($this->path.'ans_'.$ans->getToken().'.csv')){
            throw new \LogicException('Cannot update an Answer, file does not exist');
        }
        if (false === $handle = fopen($this->path.'ans_'.$ans->getToken().'.csv', 'w')){
            throw new \LogicException('Error writing to file '.$this->path.'ans_'.$ans->getToken().'.csv');
        }
        fwrite($handle, $ans->getPollId().PHP_EOL);
        $itemsArr = $ans->getItems();
        foreach ($itemsArr as $itemId => $item){
            fwrite($handle, "\"$itemId\",\"".implode('","', $item)."\"\n");
        }
        fclose($handle);
        return true;
    }
    public function findAnswer($token){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find an Answer, path to CSV file is not set');
        }
        if (!file_exists($this->path.'ans_'.$token.'.csv')){
            return false;
        }
        if (false === $file = file($this->path.'ans_'.$token.'.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error reading answer from '.$this->path.'ans_'.$token.'.csv');
        }
        $ans = new Answer($file[0]);
        $ans->setToken($token);
        $items = array();
        for ($i=1, $size = sizeof($file); $i<$size; $i++) {
            $item = str_getcsv($file[$i]);
            $items[$item[0]] = array();
            for ($j=1, $size2 = sizeof($item); $j<$size2; $j++){
                $items[$item[0]][] = $item[$j];
            }
        }
        $ans->setItems($items);
        return $ans;
    }
    public function deleteAnswer($token){
        $path = $this->path.$token;
        if (!file_exists($path)){
            throw new Exception($path.' not found!');
        }
        if (!unlink($path)){
            return false;
        } else {
            return true;
        }
    }
}