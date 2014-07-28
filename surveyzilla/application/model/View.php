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
    public $pollName;
    public $pollId;
    public $itemQuestion;
    public $options = array();
    public $loggedOff;              // TRUE when user has just logged-off
    public $stat;                   // container for statistics on a poll
    
    /**
     * Sets the message and returns the object itself. Just a useful function
     * to write less code
     * @param string $msg Message to be set
     * @return obg View object
     */
    public function setMessage($msg) {
        $this->message = $msg;
        return $this;
    }
}