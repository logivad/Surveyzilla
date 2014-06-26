<?php
namespace surveyzilla\application\model\user;
class VKUser extends SocialUser
{
    public function __construct($socialAddress){
        $this -> setLinker(VKLinker::getInstance());
        $this -> register($socialAddress);
        $this -> type = User::TYPE_SOCIAL_VK;
    }
}