<?php
namespace surveyzilla\application\model\poll;
class Item
{
    public $id;
    public $pollId;
    public $pollName;
    public $questionText;
    public $imagePath;
    public $inputType;
    public $inStat;
    public $isFinal;
    // Item with this parameter set to true is passed on to the controller
    // when the poll is finished and no custom final item exists
    public $isSystemFinal;
    public $finalLink;
    public $finalComment;
    public $options;
}