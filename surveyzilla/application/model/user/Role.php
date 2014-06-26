<?php
namespace surveyzilla\application\model\user;
class Role
{
    // Отдельно стоящий класс-синглтон для обслуживания переменной $roleset
    // объектов класса User
    const ADMIN = 1;
    const FREE = 2;
    const GOLD = 4;
    const PLATINUM = 8;
    const TEMP = 16;
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

    public function addRole($role, $roleset){
        return $role | $roleset;
    }
    public function removeRole($role){
        return $role ^ $roleset;
    }
    public function getAllRolesArr($roleset){
        $res = array();
        if (self::ADMIN & $roleset)
            $res[] = 'admin';
        if (self::FREE & $roleset)
            $res[] = 'free';
        if (self::GOLD & $roleset)
            $res[] = 'gold';
        if (self::PLATINUM & $roleset)
            $res[] = 'platinum';
        if (self::TEMP & $roleset)
            $res[] = 'temp';
        return $res;
    }
    public function getAllRolesStr($roleset){
        return implode(',', $this->getAllRolesArr($roleset));
    }
    public function isAdmin($roleset){
        return (bool) (self::ADMIN & $roleset);
    }
}