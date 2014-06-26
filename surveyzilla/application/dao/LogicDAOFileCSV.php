<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\poll\Logic;
class LogicDAOFileCSV implements ILogicDAO
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
    public function findLogic($id){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find logic, path to CSV file is not set');
        }
        if (!file_exists($this->path.'logic_'.$id.'.csv')){
            return false;
        }
        if (false === $file = file($this->path.'logic_'.$id.'.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error reading logic from '.$this->path.'logic_'.$id.'.csv');
        }
        $logicRouter = array();
        foreach ($file as $line) {
            $lineArr = str_getcsv($line);
            if (isset($logicRouter[$lineArr[0]])){
                $logicRouter[$lineArr[0]][$lineArr[1]] = $lineArr[2];
            } else {
                $logicRouter[$lineArr[0]] = array($lineArr[1] => $lineArr[2]);
            }
        }
        $logic = new Logic();
        $logic->setRouter($logicRouter);
        return $logic;
    }
}