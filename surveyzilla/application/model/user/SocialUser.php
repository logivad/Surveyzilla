<?php
namespace surveyzilla\application\model\user;
class SocialUser extends User
{
    protected $linker;
    protected function setLinker(SocialLinker $linker){
        $this -> linker = $linker;
    }
    public function register($url){
        $linkResult = $this -> linker -> link($url);
        $this -> setName($linkResult[0]);
        $this -> setEmail($linkResult[1]);
    }
}