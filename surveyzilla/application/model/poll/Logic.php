<?php
namespace surveyzilla\application\model\poll;
class Logic
{
    const END = 'end';
    private $router = array();
    /*    $router - таблица связей между ответами и вопросами
        $router[Item id][Option id] =    следующий Item id
                                        END - опрос окончен
    */
    public function setRouter(array $rt){
        $this->router = $rt;
    }
    public function getNextItem($currentItemId, $optionsArr){
        $currentOptionId = $optionsArr[0];
        return $this->router[$currentItemId][$currentOptionId];
    }
}