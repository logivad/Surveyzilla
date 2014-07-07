<?php

/* 
 * This object is filled with corresponding data and passed on to view
 */

namespace surveyzilla\application\model;
class View
{
    public $title;                  // page title
    public $message;                // message for a user (error or notice)
    public $isAuthorized;           // TRUE for authorized user
    public $isAdmin;                // TRUE for authorized admin user
    public $content;                // page content (is inserted into template)
    public $userName;               // contains user name (if authorized)
}