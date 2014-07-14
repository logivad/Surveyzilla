<?php
namespace surveyzilla\application\view;
abstract class UI
{
    // Static associative array with all texts for UI in a proper language
    public static $lang;
    /**
     * 
     * @param type $lang Which language to use ('ru')
     */
    public static function setLang($lang) {
        // Looks for $texts array for a given language and sets it as $lang
        if (file_exists("surveyzilla/lang/$lang.php")) {
            require_once "surveyzilla/lang/$lang.php";
            if (!isset($texts)) {
                throw new \RuntimeException('UI texts not set!');
            }
            self::$lang = $texts;
        } else {
            throw new \RuntimeException('Unknown language');
        }
    }
}