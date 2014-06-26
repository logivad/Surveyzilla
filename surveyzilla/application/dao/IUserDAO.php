<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\user\User;
// Интерфейс работы с репозиторием
interface IUserDAO
{
    public function addUser(User $user);
    public function updateUser(User $user);
    public function deleteUser($id);
    public function findUserById($id);
    public function findUserByEmail($id);
}