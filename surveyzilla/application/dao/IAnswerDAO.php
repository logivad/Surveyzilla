<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\poll\Answer;
// Интерфейс работы с ответами
interface IAnswerDAO
{
    public function addAnswer($pollId);
    public function updateAnswer(Answer $answer);
    public function findAnswer($token);
    public function deleteAnswer($token);
}