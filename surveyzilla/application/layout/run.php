<?php
use surveyzilla\application\Config;
use surveyzilla\application\service\PollService;
use surveyzilla\application\view\UI;
$pollService = PollService::getInstance();
?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo (isset($view->title)) ? $view->title : UI::$lang['poll'] ?></title>
        <!-- Asking IE to ignore compatibility mode -->
        <meta http-equiv= "X-UA-Compatible" content="IE=edge">
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="surveyzilla/style/run.css" />
        <link rel="stylesheet" href="surveyzilla/style/notification.css" />
        <!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="surveyzilla/js/run.js"></script>
    </head>
    <body>
        <header>
            <div class="btn-menu" title="<?php echo UI::$lang['properties'] ?>">
                <div></div><div></div><div></div>
            </div>
            <h1><?php echo isset($view->pollName) ? $view->pollName : '' ?></h1>
        </header>
        <div class="settings">
            <?php
            if (isset($view->item) && true != $view->item->isSystemFinal && true != $view->item->isFinal) {
                // Render "you can finish this poll later" block if poll is running
                echo $pollService->renderBlockLink($view, 'run');
            }elseif (isset($view->stat)) {
                // Render "you can answer this here" block for stat. page
                echo $pollService->renderBlockLink($view, 'stat');
                echo '<p><label><input type="checkbox" id="refresh"> ' . UI::$lang['stat_auto_refresh'] . '</label></p>';
            }
            ?>
        </div>
        <article>
            <?php echo $view->content ?>
        </article>
    </body>
</html>