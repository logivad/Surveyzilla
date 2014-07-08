<?php
namespace surveyzilla\application\service;
use surveyzilla\application\model\poll\Poll;
class PollService
{
    private static $_instance;
    private $pollDAO;
    private $answerDAO;
    private $logicDAO;
    private $userService;
    private function __construct(){
        /*пусто*/
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function setPollDAO($dao){
        $this->pollDAO = $dao;
    }
    public function setAnswerDAO($dao){
        $this->answerDAO = $dao;
    }
    public function setLogicDAO($dao){
        $this->logicDAO = $dao;
    }
    public function setUserService($srv){
        $this->userService = $srv;
    }
    public function findPollById($id){
        return $this->pollDAO->findPollById($id);
    }
    public function addPoll($user, $privileges, $pollName){
        $poll = new Poll($user->getId(), $pollName);
        $id = $this->pollDAO->addPoll($poll);
        if ($id < 0){
            return $id;
        }
        $user->addPoll((integer) $id);
        $this->userService->updateUser($user);
        $privileges->decrementPollNum();
        $this->userService->updateUserPrivileges($privileges);
        // Возвращаем id созданного опроса
        return $id;
    }
    public function addItem($pollId, $item){
        return $this->pollDAO->addItem($pollId, $item);
    }
    public function addAnswer($pollId){
        return $this->answerDAO->addAnswer($pollId);
    }
    public function updateAnswer($ans){
        $this->answerDAO->updateAnswer($ans);
    }
    public function findAnswer($token){
        return $this->answerDAO->findAnswer($token);
    }
    public function findLogic($id){
        return $this->logicDAO->findLogic($id);
    }
    public function isUniqueUser($pollId, $token){
        return false;
        if ($this->pollDAO->isUsedToken($pollId, $token)){
            return false;
        }
        return true;
    }
    public function addUsedToken($pollId, $token){
        $this->pollDAO->addUsedToken($pollId, $token);
    }
    public function appendResults($ans) {
        // Читаем общие результаты по опросу
        if (false === $res = $this->pollDAO->getResults()){
            // На опрос еще не отвечали, создаем массив результатов
            $poll = $this->pollDAO->findPollById($ans->getPollId());
            if ($poll === false){
                throw new \RuntimeException('Poll does not exist!');
            }
            $res = array();
            $items = $poll->getItemsArr();
            foreach($items as $item){
                $temp = array('q' => $item->getQuestion());
                foreach ($item->getOptions()->getOptionList() as $val){
                    $temp[] = array($val,'');
                }
                $res[] = $temp;
            }
        }
        /*
         * ДОДЕЛАТЬ
         */
        require_once 'surveyzilla/application/view/header.php';
        var_dump($ans);
        var_dump($res);
    }
}