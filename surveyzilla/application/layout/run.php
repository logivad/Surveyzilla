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
        <!--script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script-->
        <!--script src="surveyzilla/js/jquery-1.11.1.min.js"></script-->
        <script src="surveyzilla/js/run.js"></script>
    </head>
    <body>
        <article>
            <header>
                <?php
                if (isset($view->stat)) {
                    echo 
                    '<div class="graph-icon" title="' . UI::$lang['stat_results_info'] . '">
                        <div></div><div></div><div></div><div></div><div></div><div></div>
                    </div>';
                }
                ?>
                <div class="btn-menu" title="<?php echo UI::$lang['properties'] ?>">
                    <div></div><div></div><div></div>
                </div>

                <h1><?php echo isset($view->pollName) ? $view->pollName : '' ?></h1>
            </header>
            <aside class="settings">
                <?php
                if (isset($view->item) && true != $view->item->isSystemFinal && true != $view->item->isFinal) {
                    // Render "you can finish this poll later" block if poll is running
                    echo $pollService->renderBlockLink($view, 'run');
                }elseif (isset($view->stat)) {
                    // Render "you can answer this here" block for stat page
                    echo $pollService->renderBlockLink($view, 'stat');
                    echo '<p><label><input type="checkbox" id="refresh"> ' . UI::$lang['stat_auto_refresh'] . '</label></p>';
                }
                ?>
            </aside>
            <section>
                <?php echo $view->content ?>
            </section>
        </article>
    </body>
</html>