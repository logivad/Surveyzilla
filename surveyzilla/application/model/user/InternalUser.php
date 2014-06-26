<?php
namespace surveyzilla\application\model\user;
class InternalUser extends User
{
    private $password;
    private $hash;
    
    public function __construct(){
        $this -> type = User::TYPE_INTERNAL;
    }
    public function getPassword(){
        return $this -> password;
    }
    public function setPassword($pw){
        if ($this -> isValidPassword($pw)){
            $this -> password = $pw;
            return true;
        }
        return false;
    }
    private function isValidPassword($pw){
        return strlen($pw) > 2;
    }
    public function getHash(){
        return $this->hash;
    }
    public function setHash($hash){
        $this->hash = $hash;
    }
    public function setNewHash(){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
        $this->hash = "";
        $clen = strlen($chars) - 1;
        while (strlen($this->hash) < 10) {
            $this->hash .= $chars[mt_rand(0,$clen)];  
        }
    }
    public function isValiduser(){
        if (false === $this->isValidName($this->name) ||
            false === $this->isValidEmail($this->email) ||
            false === $this->isValidPassword($this->getPassword())){
            return false;
        }
        return true;
    }
    public function toCSV(){
        return "\"$this->id\",\"$this->name\",\"$this->email\",\"$this->type\",\"$this->roleset\",\"$this->password\",\"$this->pollList\",\"$this->hash\"";
    }
}