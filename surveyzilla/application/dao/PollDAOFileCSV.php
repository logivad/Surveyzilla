<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\poll\Poll,
    surveyzilla\application\model\poll\Item,
    surveyzilla\application\model\poll\Options;
class PollDAOFileCSV implements IPollDAO
{
    /*    Для каждого опроса создается отдельный файл. В первой строке файл
        хранится $creatorId (id пользователя, создавшего опрос) и $name (имя опроса).
        В последующих строках сохраняются объекты Item.
        Сначала нужно создать файл опроса и записать туда первую строку (метод addPoll),
        потом один за другим добавлять вопросы с вариантами ответов (метод addItem)
    */
    private $path;                        // Путь к CSV файлу с данными пользователей
    private static $_instance;            // Единственный объект класса
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
    public function addPoll(Poll $poll){
        if (!isset($this->path)){
            throw new \LogicException('Cannot save Poll, path to CSV file is not set');
        }
        // Проверяем счетчик опросов и берём id для нового опроса
        if (file_exists($this->path.'poll/poll_id.csv')){
            $id = 0 + file_get_contents($this->path.'poll/poll_id.csv');
            file_put_contents($this->path.'poll/poll_id.csv', $id+1);
        } else {
            $id = 0;
            file_put_contents($this->path.'poll/poll_id.csv', '1');
        }
        // Сохраняем новый опрос в файл (отдельный файл для каждого опроса)
        // Если файл для этого опоса уже создан - ошибка
        if (file_exists($this->path.'poll/poll_'.$id.'.csv')){
            //throw new \Exception('Cannot add poll because '.$this->path.'poll_'.$id.'.csv already exists');
            return -1;
        }
        // Создаём файл для опроса и записываем первую строку - id создателя и название опроса
        if (false === $handle = fopen($this->path.'poll/poll_'.$id.'.csv','w')){
            //throw new \RuntimeException('Error creating '.$this->path.'poll_'.$id.'.csv','w');
            return -2;
        }
        if (false === fwrite($handle,'"'.$poll->getCreatorId().'","'.$poll->getName().'"'.PHP_EOL)){
            //throw new \RuntimeException('Error writing to file '.$this->path.'poll_'.$id.'.csv');
            return -3;
        }
        return fclose($handle) ? $id : -4;
    }
    public function addItem($pollId, Item $item){
        // Каждый Item представлен в файле своего опроса в виде одной строки (начиная со второй):
        // $itemId $question   $type $hasCustomField option_0 option_1 option_2... ($optionList elements)
        // |__ Item _______|   |___________ Options _____...
        if (!isset($this->path)){
            throw new \LogicException('Cannot save Item, path to CSV file is not set');
        }
        if (!file_exists($this->path.'poll/poll_'.$pollId.'.csv')){
            throw new \Exception('Cannot add item because '.$this->path.'poll/poll_'.$pollId.'.csv does not exist');
        }
        if (false === $file = file($this->path.'poll/poll_'.$pollId.'.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error creating '.$this->path.'poll/poll_'.$pollId.'.csv');
        }
        $file[] = '"'.(sizeof($file)-1).'",'.$item->toCSV();
        $handle = fopen($this->path.'poll/poll_'.$pollId.'.csv','w');
        if (false === $handle){
            throw new \RuntimeException('Error creating '.$this->path.'poll/poll_'.$pollId.'.csv','w');
        }
        foreach ($file as $val){
            fwrite($handle, $val.PHP_EOL);
        }
        fclose($handle);
        return true;
    }
    public function deletePoll($id){
    }
    public function findPollById($pollId){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find Poll, path to CSV file is not set');
        }
        if (!file_exists($this->path.'poll/poll_'.$pollId.'.csv')){
            return false;
        }
        if (false === $file = file($this->path.'poll/poll_'.$pollId.'.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error extracting poll from '.$this->path.'poll/poll_'.$pollId.'.csv');
        }
        $tempArr = str_getcsv($file[0]);
        $poll = new Poll($tempArr[0], $tempArr[1]);
        $poll->setId($pollId);
        for ($i=1, $size=sizeof($file); $i<$size; $i++){
            $tempArr = str_getcsv($file[$i]);
            $item = new Item($tempArr[1]);
            $item->setId($tempArr[0]);
            //создаем объект Options
            $optionList = array_slice($tempArr,4);
            $options = new Options($tempArr[2], $tempArr[3], $optionList);
            $item->setOptions($options);
            $poll->addItem($item);
        }
        return $poll;
    }
    public function isUsedToken($pollId, $token){
        // если файла еще нет, значит никто еще не отвечал на этот вопрос
        if (!file_exists($this->path.'used_tokens/'.$pollId.'.txt')){
            return false;
        }
        if (false === $file = file($this->path.'used_tokens/'.$pollId.'.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error reading file with used tokens');
        }
        return (false !== array_search($token, $file)) ? true : false;
    }
    public function addUsedToken($pollId, $token){
        if (false === file_put_contents($this->path.'used_tokens/'.$pollId.'.txt', $token.PHP_EOL, FILE_APPEND)){
            throw new \RuntimeException('Error adding used token');
        }
    }
    public function getResults() {
        return false;
    }
}