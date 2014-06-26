<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\user\User,
    surveyzilla\application\model\user\Role,
    surveyzilla\application\service\UserService;
class UserDAOFileCSV implements IUserDAO
{
    // путь к CSV файлу с данными пользователей
    private $path;
    private $service;
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

    public function setPath($path){
        $this->path = $path;
    }
    public function setService(UserService $srv){
        $this->service = $srv;
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
        if (!isset($this->path)){
            throw new \LogicException('Cannot save User, path to CSV file is not set');
        }
        if (!$user->isValidUser()){
            throw new \LogicException('Cannot save User, user data is not correct');
        }
        if (file_exists($this->path.'users.csv')){
            // Если файл с данными пользователей уже есть, то обновляем данные пользователя
            if (false === $file = file($this->path.'users.csv',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
                throw new \RuntimeException('Error opening CSV file');
            }
            for ($userLine=0, $size=sizeof($file); $userLine<$size; $userLine++){
                $userData = str_getcsv($file[$userLine]);
                if ($userData[0] == $user->getId()){
                    // Пользователь найден в базе (id - нулевое поле)
                    $file[$userLine] = $user->toCSV();
                    // Обновляем данные пользователя в файле
                    $this->saveUsersToFile($file);
                    return true;
                }
            }
            // Пользователь не найден
            return false;
        } else {
            // Если файла с базой пользователей нет - ошибка
            throw new \Exception('CSV file not found. If this user is the first one, use addUser() instead');
        }
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
    public function findUserById($id){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find User, path to CSV file is not set');
        }
        if (!file_exists($this->path.'users.csv')){
            return false;
        }
        if (false === $file = file($this->path.'users.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \Exception('Cannot read CSV file');
        }
        foreach ($file as $userLine){
            $userData = str_getcsv($userLine);
            if ($userData[0] == $id){
                // Нашли данные пользователя в файле, создаем объект
                $user = $this->service->createUserByType($userData[3]);
                // Инициализируем пользователя (общие свойства)
                $user->setId($userData[0]);
                $user->setName($userData[1]);
                $user->setEmail($userData[2]);
                $user->setType($userData[3]);
                $user->setRoleset($userData[4]);
                // Только у InternalUser есть пароль, установим его
                if ($user->getType() == User::TYPE_INTERNAL){
                    $user->setPassword($userData[5]);
                    $user->setHash($userData[7]);
                }
                $user->setPollList($userData[6]);
                return $user;
            }
        }
        return false;
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
    public function findUserByEmail($email){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find User, path to CSV file is not set');
        }
        if (!file_exists($this->path.'users.csv')){
            return false;
        }
        if (false === $file = file($this->path.'users.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            return false;
            //throw new \Exception('Cannot read CSV file');
        }
        foreach ($file as $userLine){
            $userData = str_getcsv($userLine);
            if ($userData[2] == $email){
                // Нашли данные пользователя в файле, создаем объект
                $user = $this->service->createUserByType($userData[3]);
                // Инициализируем пользователя (общие свойства)
                $user->setId($userData[0]);
                $user->setName($userData[1]);
                $user->setEmail($userData[2]);
                $user->setType($userData[3]);
                $user->setRoleset($userData[4]);
                // Только у InternalUser есть пароль, установим его
                if ($user->getType() == User::TYPE_INTERNAL){
                    $user->setPassword($userData[5]);
                    $user->setHash($userData[7]);
                }
                $user->setPollList($userData[6]);
                return $user;
            }
        }
        return false;
    }
}