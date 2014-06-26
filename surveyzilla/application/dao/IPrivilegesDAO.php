<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\user\Privileges;
interface IPrivilegesDAO
{
    public function findUserPrivilegesById($id);
    public function deletePrivileges($id);
    public function updateUserPrivileges(Privileges $privileges);
    public function addUserPrivileges(Privileges $privileges);
}