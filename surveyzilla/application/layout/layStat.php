<?php
use surveyzilla\application\Config;
use surveyzilla\application\service\PollService;
use surveyzilla\application\view\UI;
$pollService = PollService::getInstance();
?><!DOCTYPE html>
<html lang="<?php echo Config::$lang ?>">
    <head>
        <title><?php echo (isset($view->title)) ? $view->title : UI::$lang['poll'] ?></title>
        <!-- Asking IE to ignore compatibility mode -->
        <meta http-equiv= "X-UA-Compatible" content="IE=edge">
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- saved from url=(0014)about:internet -->
        <!--[if lt IE 9]>
            <script src="js/html5shiv.js"></script>
        <![endif]-->
        <link rel="stylesheet" href="surveyzilla/style/run.css" />
        <script src="surveyzilla/js/run_stat.js"></script>
    </head>
    <body>
        <main>
            <header>
                <div class="graph-icon" title="<?php echo UI::$lang['stat_results_info'] ?>">
                    <div></div><div></div><div></div><div></div><div></div><div></div>
                </div>
                <div class="btn-menu" title="<?php echo UI::$lang['properties'] ?>">
                    <div></div><div></div><div></div>
                </div>
                <h1><?php echo isset($view->pollName) ? $view->pollName : '' ?></h1>
            </header>
            <aside class="settings">
                <h2 style="display: none;"><?php echo UI::$lang['poll_settings'] ?></h2>
                <?php
                // Echo "you can answer the poll here" link
                $linkPoll = 'http://' . Config::$domain . '/index.php?a=run&poll=' . $view->pollId;
                echo '<p>' . UI::$lang['link_to_poll'] . ' <a href="' 
                . $linkPoll . '" target="_blank">' . UI::$lang['here'] . '</a></p>';
                ?>
                <p><label><input type="checkbox" id="refresh"><?php echo UI::$lang['stat_auto_refresh'] ?></label></p>
            </aside>
            <div class="content-wrapper">
                <?php echo $view->content ?>
            </div>
        </main>
    </body>
</html>