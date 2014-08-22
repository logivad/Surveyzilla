<?php
namespace surveyzilla\application;
class Config
{
    /*
     * Database parameters
     */
    public static $dbHost = 'localhost';
    public static $dbName = 'surveyzilla';
    public static $dbUser = 'sz';
    public static $dbPass = 'Zv2yTXstX9RMpanZ';
    public static $dbPassSalt = 'x9gZhq!pgh';
    /*
     * Website parameters
     */
    // Must be a valid BCP 47 language tag, is used in <html> tag
    public static $lang = 'ru';
    public static $domain = 'surveyzilla.dev';
    public static $tempAnsDir = 'temp/';
    public static $cacheDir = 'cache/';
}
