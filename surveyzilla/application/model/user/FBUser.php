<?php
namespace surveyzilla\application\model\user;
class FBUser extends SocialUser
{
    public function __construct($socialAddress){
        $this->setLinker(FBLinker::getInstance());
        $this->register($socialAddress);
        $this->type = User::TYPE_SOCIAL_FB;
    }
}