<?php
namespace surveyzilla\application\model\user;
class VKLinker extends SocialLinker
{
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
    public function link($url){
        return array('vk_user'.rand(1000,9000),rand(1000,9000).'@gmail.com');
    }
}