<?php
namespace surveyzilla\application\model\user;
class Privileges
{
    const POLLS_NUM_FREE = 5;            // сколько пользователь может создать опросов
    const POLLS_NUM_GOLD = 100;
    const POLLS_NUM_PLATINUM = 1000;
    const POLLS_NUM_ADMIN = 100;
    const POLLS_NUM_TEMP = 1;

    const ANS_NUM_FREE = 1000;            // сколько пользователь может собрать ответов в месяц
    const ANS_NUM_GOLD = 10000;            // (по всем созданным опросам)
    const ANS_NUM_PLATINUM = 1000000;
    const ANS_NUM_ADMIN = 100;
    const ANS_NUM_TEMP = 100;

    private $id;
    private $availPollNum=0;            // сколько может создать опросов
    private $availAnsNum=0;                // сколько может собрать ответов
    private $canUseLogic=0;                // разрешение использовать логику
    private $canUsePassword=0;            // разрешение использовать доступ к опросам по паролю
    private $canUseCustomLogo=0;        // разрешение использовать личный логотип
    private $canUseFinalPage=0;            // разрешение использовать индивидуальныйе страницы
    private $deepReropting=0;            // полный отчет, а не только статистика


    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function getPrivilegesArr(){
        // Возвращает массив с переменными
        // Порядок в этом массиве очень важен! Используется при сохранении в CSV!
        return array(
            'id' => $this->id,
            'availPollNum' => $this->availPollNum,
            'availAnsNum' => $this->availAnsNum,
            'canUseLogic' => (bool) $this->canUseLogic,
            'canUsePassword' => (bool) $this->canUsePassword,
            'canUseCustomLogo' => (bool) $this->canUseCustomLogo,
            'canUseFinalPage' => (bool) $this->canUseFinalPage,
            'deepReropting' => (bool) $this->deepReropting
            );
    }
    public function canCreatePoll(){
        return ($this->availPollNum>0) ? true : false;
    }
    public function initialize(array $params){
        $this->id = $params[0];
        $this->availPollNum = $params[1];
        $this->availAnsNum = $params[2];
        $this->canUseLogic = (bool) $params[3];
        $this->canUsePassword = (bool) $params[4];
        $this->canUseCustomLogo = (bool) $params[5];
        $this->canUseFinalPage = (bool) $params[6];
        $this->deepReropting = (bool) $params[7];
    }
    public function toCSV(){
        $strCSV = '';
        $privArr = $this->getPrivilegesArr();
        foreach ($privArr as $val){
            $strCSV .= '"'.$val.'",';
        }
        return substr($strCSV, 0, -1);
    }
    public function decrementPollNum(){
        $this->availPollNum --;
    }
    public function decrementAns(){
        $this->availAnsNum --;
    }
    public function getAvailPollNum(){
        return $this->availPollNum;
    }
    public function getAvailAnsNum(){
        return $this->availAnsNum;
    }
    public function setPrivilegesByRole($role){
        switch ($role){
            case Role::FREE:
                $this->availPollNum = self::POLLS_NUM_FREE;
                $this->availAnsNum = self::ANS_NUM_FREE;
                break;
            case Role::GOLD:
                $this->availPollNum = self::POLLS_NUM_GOLD;
                $this->availAnsNum = self::ANS_NUM_GOLD;
                $this->canUseLogic=1;
                $this->canUsePassword = 1;
                $this->canUseCustomLogo = 1;
                $this->canUseFinalPage = 1;
                $this->deepReropting = 1;
                break;
            case Role::PLATINUM:
                $this->availPollNum = self::POLLS_NUM_PLATINUM;
                $this->availAnsNum = self::ANS_NUM_PLATINUM;
                $this->canUseLogic=1;
                $this->canUsePassword = 1;
                $this->canUseCustomLogo = 1;
                $this->canUseFinalPage = 1;
                $this->deepReropting = 1;
                break;
            case Role::ADMIN:
                $this->availPollNum = self::POLLS_NUM_ADMIN;
                $this->availAnsNum = self::ANS_NUM_ADMIN;
                $this->canUseLogic=1;
                $this->canUsePassword = 1;
                $this->canUseCustomLogo = 1;
                $this->canUseFinalPage = 1;
                $this->deepReropting = 1;
                break;
            case Role::TEMP:
                $this->availPollNum = self::POLLS_NUM_TEMP;
                $this->availAnsNum = self::ANS_NUM_TEMP;
                break;
        }
    }
}