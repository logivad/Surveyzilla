<?php

use surveyzilla\application\Config;
use surveyzilla\application\view\UI;
?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo (isset($view->title)) ? $view->title : UI::$lang['poll'] ?></title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="surveyzilla/style/run.css" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="surveyzilla/js/run.js"></script>
    </head>
    <body>
        <div class="main-content">
            <div class="title-bar">
                <div class="menu">
                    <div class="menu-line"></div>
                    <div class="menu-line"></div>
                    <div class="menu-line"></div>
                </div>
                <h1><?php echo (isset($view->pollName)) ? $view->pollName : '' ?></h1>
            </div>
            <div class="settings">
                <?php
                if (isset($view->item)) {
                    // Rendering settings block content for poll running
                    echo '<p>' . UI::$lang['finish_poll_later'] 
                    . '<input class="input-text-wide" type="text" value="http://' . Config::$domain . '/index.php?a=run&poll=' . $view->item->pollId . '" /></p>';
                }elseif (isset($view->stat)) {
                    // Rendering settings block content for poll statistics
                    echo '<p>' . UI::$lang['link_to_poll'] 
                    . '<input class="input-text-wide" type="text" value="http://' . Config::$domain . '/index.php?a=run&poll=' . $view->pollId . '" /></p>';
                }
                ?>
            </div>
            <div class="item-content">
                <?php echo $view->content ?>
            </div>
        </div>
    </body>
</html>