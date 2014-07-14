<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $view->title ?></title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="surveyzilla/style/style.css" />
    </head>
    <body>
        <?php require_once 'surveyzilla/application/view/widget/mainmenu.php'; ?>
        <br />
        <?php echo $view->content ?>
        
    </body>
</html>