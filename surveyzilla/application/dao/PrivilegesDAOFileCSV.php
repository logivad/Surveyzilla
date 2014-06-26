<?php
namespace surveyzilla\application\dao;
use surveyzilla\application\model\user\Privileges;
class PrivilegesDAOFileCSV implements IPrivilegesDAO
{
    // путь к CSV файлу с привилегиями пользователей
    private $path;
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
    private function savePrivilegesToFile(&$arr){
        if (false === $handle = fopen($this->path.'privileges.csv','w')){
            throw new \RuntimeException('Error updating CSV file');
        }
        foreach ($arr as $val){
            fwrite($handle, $val.PHP_EOL);
        }
        fclose($handle);
    }
    public function addUserPrivileges(Privileges $privileges){
        if (!isset($this->path)){
            throw new \LogicException('Cannot save Privileges, path to CSV file is not set');
        }
        // Сохраняем привилегии пользователя в файл
        if (file_exists($this->path.'privileges.csv')){
            // Если файл с данными пользователей уже есть, добавляем пользователя и сохраняем
            if (false === $file = file($this->path.'privileges.csv',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
                throw new \RuntimeException('Error opening CSV file');
            }
            // проверка уникальности
            foreach ($file as $val){
                $line = str_getcsv($val);
                if ($line[0] == $privileges->getId()){
                    throw new \LogicException('Cannot save. Privileges of the same id is already in the CSV file!');
                }
            }
            $file[] = $privileges->toCSV();
            // Записываем данные в файл
            $this->savePrivilegesToFile($file);
        } else {
            // Если файла с базой привилегий пользователей нет, создаем его и записываем
            $file = array($privileges->toCSV());
            $this->savePrivilegesToFile($file);
        }
        return true;
    }
    public function updateUserPrivileges(Privileges $privileges){
        if (!isset($this->path)){
            throw new \LogicException('Cannot save Privileges, path to CSV file is not set');
        }
        if (file_exists($this->path.'privileges.csv')){
            // Если файл с данными пользователей уже есть, то обновляем данные пользователя
            if (false === $file = file($this->path.'privileges.csv',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
                throw new \RuntimeException('Error opening CSV file');
            }
            for ($privLine=0, $size=sizeof($file); $privLine<$size; $privLine++){
                $privData = str_getcsv($privLine);
                if ($privData[0] == $privileges->getId()){
                    // Пользователь найден в базе (id - нулевое поле), обновляем его данные
                    $file[$privLine] = $privileges->toCSV();
                    // Обновляем данные пользователя в файле
                    $this->savePrivilegesToFile($file);
                    return true;
                }
            }
            // Пользователь не найден
            return false;
        } else {
            // Если файла с базой привилегий нет - ошибка
            throw new \Exception('CSV file not found. If this Privileges ojbect is the first to save, use saveNewPrivileges() instead');
        }
    }
    public function deletePrivileges($id){
        if (false === $file = file($this->path.'privileges.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error opening CSV file');
        }
        for ($privLine=0, $size=sizeof($file); $privLine<$size; $privLine++){
            $privData = str_getcsv($file[$privLine]);
            if ($privData[0] == $id){
                // Пользователь найден в базе (id - нулевое поле)
                unset($file[$privLine]);
                $this->savePrivilegesToFile($file);
                return true;
            }
        }
        return false;
    }
    public function deleteAllPrivileges(){
        if (false === $file = file($this->path.'privileges.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            throw new \RuntimeException('Error opening CSV file');
        }
        return file_put_contents($this->path.'privileges.csv', '');
    }
    public function findUserPrivilegesById($id){
        if (!isset($this->path)){
            throw new \LogicException('Cannot find Privileges, path to CSV file is not set');
        }
        if (false === is_readable($this->path.'privileges.csv') || false === $file = file($this->path.'privileges.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
            return false;
        }
        foreach ($file as $privLine){
            $privData = str_getcsv($privLine);
            if ($privData[0] == $id){
                // Нашли данные привилегий пользователя в файле, создаем объект
                $privileges = new Privileges();
                // Инициализируем - передаем методу объекта привилегий массив с данными, а уже он сделает всю работу
                $privileges->initialize($privData);
                return $privileges;
            }
        }
        return false;
    }
}
