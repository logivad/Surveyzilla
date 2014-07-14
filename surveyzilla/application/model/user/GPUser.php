<?php
namespace surveyzilla\application\model\user;
class GPUser extends SocialUser
{
    public function __construct($socialAddress){
        $this->setLinker(GPLinker::getInstance());
        $this->register($socialAddress);
        $this->type = User::TYPE_SOCIAL_GP;
    }
}