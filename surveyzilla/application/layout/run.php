<?php
use surveyzilla\application\Config;
use surveyzilla\application\service\PollService;
use surveyzilla\application\view\UI;
$pollService = PollService::getInstance();
?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo (isset($view->title)) ? $view->title : UI::$lang['poll'] ?></title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="surveyzilla/style/run.css" />
        <link rel="stylesheet" href="surveyzilla/style/notification.css" />
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
                <h1><?php echo isset($view->pollName) ? $view->pollName : '' ?></h1>
            </div>
            <div class="settings">
                <?php
                if (isset($view->item) && true != $view->item->isSystemFinal && true != $view->item->isFinal) {
                    // Render "you can finish this poll later" block if poll is running
                    echo $pollService->renderBlockLink($view, 'run');
                }elseif (isset($view->stat)) {
                    // Render "you can answer this here" block for stat. page
                    echo $pollService->renderBlockLink($view, 'stat');
                    echo '<label><input type="checkbox" id="refresh"> ' . UI::$lang['stat_auto_refresh'] . '</label>';
                }
                ?>
            </div>
            <div class="item-content">
                <?php echo $view->content ?>
            </div>

        </div>
    </body>
</html>