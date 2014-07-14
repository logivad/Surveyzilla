<?php
namespace surveyzilla\application\model\user;
abstract class SocialLinker
{
    abstract public function link($url);
}