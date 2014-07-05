<?php
namespace surveyzilla\application\service;
use surveyzilla\application\model\user\User, surveyzilla\application\model\user\FBUser, surveyzilla\application\model\user\VKUser,
surveyzilla\application\model\user\GPUser, surveyzilla\application\model\user\InternalUser;
class UserService
{
    private static $_instance;
    private $userDAO;
    private $userPrivilegesDAO;
    private function __construct(){
        /*пусто*/
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function setUserDAO($dao){
        $this->userDAO = $dao;
    }
    public function setUserPrivilegesDAO($dao){
        $this->userPrivilegesDAO = $dao;
    }
    public function findUserById($id){
        return $this->userDAO->findUserById($id);
    }
    public function getAllUsers(){
        return $this->userDAO->getAllUsers();
    }
    public function getUserTypeById($id){
        return $this->userDAO->getUserTypeById($id);
    }
    public function findUserByEmail($email){
        return $this->userDAO->findUserByEmail($email);
    }
    public function findUserPrivilegesById($id){
        return $this->userPrivilegesDAO->findUserPrivilegesById($id);
    }
    public function addUser($user){
        return $this->userDAO->addUser($user);
    }
    public function addUserPrivileges($privileges){
        return $this->userPrivilegesDAO->addUserPrivileges($privileges);
    }
    public function updateUser($user){
        return $this->userDAO->updateUser($user);
    }
    public function updateUserPrivileges($privileges){
        $this->userPrivilegesDAO->updateUserPrivileges($privileges);
    }
    public function deleteUser($id){
        if (false === $this->userDAO->deleteUser($id) ||
            false === $this->userPrivilegesDAO->deletePrivileges($id)){
            return false;
        }
        return true;
    }
    /*public function deleteAllUsers(){
        if (false === $this->userDAO->deleteAllUsers() ||
        false === $this->userPrivilegesDAO->deleteAllPrivileges()){
            return false;
        }
        return true;
    }*/
    public function authorize($email, $password){
        // Проверяет авторизацию пользователя по куки возвращает TRUE / FALSE
        $user = $this->userDAO->findUserByEmail($email);
        if ($user === false || !$password){
            return false;
        }
        if ($user->getPassword() === md5(md5($password))){
            // генерируем хэш и устанавливаем куки
            $user->setNewHash();
            $this->userDAO->updateUser($user);
            setcookie("uid", $user->getId(), time()+60*60*24*30);
            setcookie("hash", $user->getHash(), time()+60*60*24*30);
            return true;
        }
        return false;
    }
    public function quit(){
        if (setcookie('uid', '', time()-1000)
            && setcookie('hash', '', time()-1000)){
            return true;
        }
        return false;
    }
    public function isAuthorized($data=null){
        /* Проверяет авторизацию пользователя по куки
         * Если авторизован - возвращает ссылку на объект пользователя $user,
         * иначе - false (или нетронутый $data, если задан)
         * Если передан необязательный аргумент $data и пользователь авторизован,
         * вместо объекта будет возвращен объект $data, наполненный данными
         * о пользователе. Объект $data должен быть stdClass
         */
        if (isset($_COOKIE['uid']) && isset($_COOKIE['hash'])){
            if (false === $user = $this->userDAO->findUserById($_COOKIE['uid'])){
                if ($data !== null){
                    return $data;
                }
                return false;
            }
            if ($user->getHash() === $_COOKIE['hash']){
                // Пользователь авторизован. Определяем что возвращать
                if ($data === null){
                    // Возвращаем пользователя
                    return $user;
                } else {
                    // Наполняем объект данными о пользователе
                    $data->isAuthorized = true;
                    $data->userName = $user->getName();
                    if ($user->isAdmin()){
                        $data->isAdmin = true;
                    }
                    return $data;
                }
            } else {
                if ($data !== null){
                    return $data;
                }
                return false;
            }
        } else {
            if ($data !== null){
                return $data;
            }
            return false;
        }
    }
    public function isAuthorizedAdmin(){
        $user = $this->isAuthorized();
        if ($user && method_exists($user, 'isAdmin')&& $user->isAdmin()){
            return true;
        } else {
            return false;
        }
    }
    public function isAuthorizedUserId($id){
        $user = $this->isAuthorized();
        if ($user !== false && method_exists($user, 'getId')&& $user->getId() === $id){
            return true;
        } else {
            return false;
        }
    }
    public function createUserByType($type){
        switch ($type){
            case User::TYPE_SOCIAL_FB:
                $user = new FBUser();
                break;
            case User::TYPE_SOCIAL_VK:
                $user = new VKUser();
                break;
            case User::TYPE_SOCIAL_GP:
                $user = new GPUser();
                break;
            case User::TYPE_INTERNAL:
                $user = new InternalUser();
                break;
            default:
                throw new \LogicException('Invalid User type!');
        }
        return $user;
    }
}