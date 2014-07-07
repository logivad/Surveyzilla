<?php

/*
 * Handles database connection.
 * Typical usage:
 *      ...
 *      DbConnection::getInstance()->getHandler();
 *      $dbh->prepare(...);
 *      ...
 *      DbConnection::getInstance()->unsetHandler();
 *      unset($dbh);
 */

namespace surveyzilla\application\dao;
use surveyzilla\application\Config;

class DbConnection
{
    private static $_instance;
    private static $_dbh;
    
    private function __construct(){
        /*empty*/
    }
    public static function getInstance(){
        if (null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function getHandler() {
        if (empty($this->_dbh)) {
            try {
                $this->_dbh = new \PDO(
                    'mysql:host='.Config::$dbHost.';dbname='.Config::$dbName,
                    Config::$dbUser,
                    Config::$dbPass
                );
                $this->_dbh->exec("SET NAMES 'utf8'");
            } catch (\PDOException $exc) {
                throw new Exception('Database connection failed');
            }
        }
        return $this->_dbh;
    }
    public function unsetHandler() {
        unset($this->_dbh);
    }
}