<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\poll\Poll,
    surveyzilla\application\model\poll\Item;
// Интерфейс работы с репозиторием
interface IPollDAO
{
    public function addPoll(Poll $poll);
    public function deletePoll($id);
    public function findPollById($id);
    public function addItem($pollId, Item $item);
}