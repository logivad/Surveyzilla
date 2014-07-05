<?php
namespace surveyzilla\application\model\user;
class User
{
    // Типы пользователей
    const TYPE_INTERNAL = 0;
    const TYPE_SOCIAL_FB = 1;
    const TYPE_SOCIAL_VK = 2;
    const TYPE_SOCIAL_GP = 3;
    protected $id;
    protected $name;
    protected $email;
    protected $type;
    protected $roleset;
    protected $pollList='';

    public function getId(){
        return $this -> id;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function getPollList(){
        return $this->pollList;
    }
    public function setPollList($list){
        $this->pollList = $list;
    }
    public function addPoll($id){
        if (!is_int($id)){
            throw new \LogicException('$id must be Integer!');
        }
        if ($this->pollList === ''){
            $this->pollList .= $id;
        } else {
            $this->pollList .= ','.$id;
        }
        return true;
    }
    public function hasPoll($id){
        $polls = explode(',', $this->pollList);
        return (false === array_search($polls, $id)) ? false : true;
    }
    public function removePoll($id){
        if (!is_int($id)){
            throw new \LogicException('$id must be Integer!');
        }
        $polls = explode(',', $this->pollList);
        $target_key = array_search($polls, $id);
        if ($target_key === false){
            return false;
        }
        unset($polls[$target_key]);
        $this->pollList = implode(',',$polls);
        return true;
    }
    public function getName(){
        return $this -> name;
    }
    public function setName($name){
        if ($this -> isValidName($name))
            $this -> name = $name;
    }
    protected function isValidName($name){
        /*переделать*/
        return strlen($name) > 1;
    }
    public function getEmail(){
        return $this -> email;
    }
    public function setEmail($email){
        if ($this -> isValidEmail($email)){
            $this -> email = $email;
            return true;
        }
        return false;
    }
    protected function isValidEmail($email){
        if (!strpos($email,'@')) return false;
        if (!strpos($email,'.')) return false;
        return true;
    }
    public function isValiduser(){
        if (false === $this->isValidName($this->name) || false === $this->isValidEmail($this->email)){
            return false;
        }
        return true;
    }
    public function getRoleset(){
        return $this->roleset;
    }
    public function setRoleset($role){
        $this->roleset = $role;
    }
    public function getType(){
        return $this -> type;
    }
    public function setType($type){
        $this->type = $type;
    }
    public function getTypeStr(){
        switch ($this->type){
            case self::TYPE_INTERNAL: return 'internal';
            case self::TYPE_SOCIAL_FB: return 'social_fb';
            case self::TYPE_SOCIAL_VK: return 'social_vk';
            case self::TYPE_SOCIAL_GP: return 'social_gp';
        }
        return 'unknown_type';
    }
    public function getUserRole(){
        return $this -> userRole;
    }
    public function getUserRights(){
        return $this -> userRights;
    }
    public function isAdmin(){
        return Role::getInstance()->isAdmin($this->roleset);
    }
    public function setNewHash(){
        throw new \LogicException('Wrong type of User!');
    }
}