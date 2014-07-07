<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\user\User,
    surveyzilla\application\model\user\Role,
    surveyzilla\application\service\UserService,
    surveyzilla\application\Config,
    surveyzilla\application\dao\DbConnection;
class UserDAOMySQL implements IUserDAO
{
    private $service;
    private static $_instance;
    
    private function __construct(){}
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
            self::$_instance->service = UserService::getInstance();
        }
        return self::$_instance;
    }
    
    public function setService(UserService $srv){
        $this->service = $srv;
    }
    public function findUser($searchBy, $needle){
        if ($searchBy == 'id') {
            $sql = "SELECT `Users`.`Id`, `Title` AS 'Role', `Type`, `Email`, `Name`, "
                 . "`Password`, `Hash`, `RegDate` FROM `Users` INNER JOIN `UserRoles` "
                 . "ON `RoleId` = `UserRoles`.`Id` AND `Users`.`Id` = $needle";
        } elseif ($searchBy == 'email') {
            $sql = "SELECT `Users`.`Id`, `Title` AS 'Role', `Type`, `Email`, `Name`, "
                 . "`Password`, `Hash`, `RegDate` FROM `Users` INNER JOIN `UserRoles` "
                 . "ON `RoleId` = `UserRoles`.`Id` AND `Users`.`Email` = '$needle'";
        } else {
            return false;
        }
        $dbh = DbConnection::getInstance()->getHandler();
        $stmt = $dbh->query($sql);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (empty($userData)) {
            return false;
        }
        // Создаем объект User
        $user = $this->service->createUserByType(
            $userData['Type'],
            $userData['Id'],
            $userData['Role'],
            $userData['RegDate']
        );
        // Инициализируем пользователя (общие свойства)
        $user->setName($userData['Name']);
        $user->setEmail($userData['Email']);
        // Свойства InternalUser
        if ($user->getType() == User::TYPE_INTERNAL){
            $user->setPassword($userData['Password']);
            $user->setHash($userData['Hash']);
        }
        // Перечь опросов пользователя
        $sql = "SELECT `Id`, `Name` FROM `Polls` WHERE `UserId` = {$userData['Id']}";
        $pollList = array();
        foreach($dbh->query($sql, \PDO::FETCH_ASSOC) as $record) {
            $pollList[$record['Id']] = $record['Name'];
        }
        $dbh = null;
        $user->setPollList($pollList);
        return $user;
    }
    private function saveUsersToFile(&$arr){
        if (false === $handle = fopen($this->path.'users.csv','w')){
            throw new \RuntimeException('Error updating CSV file');
        }
        foreach ($arr as $val){
            fwrite($handle, $val.PHP_EOL);
        }
        fclose($handle);
    }
    public function addUser(User $user){
        function getUniqueId($path){
            // Проверяем счетчик пользователей и берём id для нового пользователя
            if (file_exists($path.'user_id.csv')){
                $id = 0 + file_get_contents($path.'user_id.csv');
                file_put_contents($path.'user_id.csv', $id+1);
            } else {
                $id = 0;
                file_put_contents($path.'user_id.csv', '1');
            }
            return $id;
        }
        if (!isset($this->path)){
            throw new \LogicException('Cannot save User, path to CSV file is not set');
        }
        if (!$user->isValidUser()){
            throw new \LogicException('Cannot save User, user data is not correct');
        }
        // Сохраняем нового пользователя в файл
        if (file_exists($this->path.'users.csv')){
            // Если файл с данными пользователей уже есть, добавляем пользователя и сохраняем
            if (false === $file = file($this->path.'users.csv',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
                throw new \RuntimeException('Error opening CSV file');
            }
            // Проверка на уникальность email
            foreach ($file as $userLine){
                $userData = str_getcsv($userLine);
                if ($userData[2] === $user->getEmail()){
                    throw new \LogicException('Cannot save User. Email must be unique');
                }
            }
            $user->setId(getUniqueId($this->path));
            $file[] = $user->toCSV();
        } else {
            // Если файла с базой пользователей нет, создаем его
            $user->setId(getUniqueId($this->path));
            $file = array($user->toCSV());
        }
        // Записываем данные пользователя в файле
        $this->saveUsersToFile($file);
        return true;
    }
    public function updateUser(User $user){
        if (!$user->isValidUser()){
            throw new \LogicException('Cannot save User, user data is not correct');
        }
        $sql = "UPDATE Users SET "
             . "`Email` = '{$user->getEmail()}', "
             . "`Name` = '{$user->getName()}', "
             . "`Password` = '{$user->getPassword()}', "
             . "`Hash` = '{$user->getHash()}' "
             . "WHERE `Id` = {$user->getId()}";
        $dbh = DbConnection::getInstance()->getHandler();
        $dbh->exec($sql);
        $dbh = null;
    }
    public function deleteUser($id){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find User, path to CSV file is not set');
        }
        if (false === $file = file($this->path.'users.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error opening CSV file');
        }
        for ($userLine=0, $size=sizeof($file); $userLine<$size; $userLine++){
            $userData = str_getcsv($file[$userLine]);
            if ($userData[0] == $id){
                // Пользователь найден в базе (id - нулевое поле)
                unset($file[$userLine]);
                $this->saveUsersToFile($file);
                return true;
            }
        }
        return false;
    }
    /*public function deleteAllUsers(){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find User, path to CSV file is not set');
        }
        if (false === file_put_contents($this->path.'users.csv', '') ||
            false === file_put_contents($this->path.'user_id.csv', '')){
            return false;
        }
        return true;
    }*/
    public function getUserTypeById($id){
        if (false === $user = $this->findUserById($id)){
            throw new \Exception('Cannot find User!');
        }
        return $user->getType();
    }
    public function getAllUsers(){
        if (!isset($this->path)){
            throw new \LogicException('Cannot get users, path to CSV file is not set');
        }
        if (!file_exists($this->path.'users.csv')){
            return null;
        }
        if (false === $file = file($this->path.'users.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \Exception('Cannot read CSV file');
        }
        return $file;
    }

}