<!DOCTYPE html>
<html>
    <head>
        <title><?php echo (isset($view->title)) ? $view->title : 'Surveyzilla' ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="surveyzilla/style/style.css" />
    </head>
    <body>
        <div id="box_centered">
            <?php echo $view->content ?>
        </div>
    </body>
</html>