<?php
use surveyzilla\application\view\UI;
?>
        <form action="index.php" method="GET">
            <p class="question"><?php echo $view->item->questionText ?></p>
            <?php
            if (isset($view->item->imagePath)) {
                echo "<img src='{$view->item->imagePath}' />\n";
            }
            ?>
            <div class="options">
                <?php
                foreach ($view->item->options as $key => $val) {
                    echo "<label><input type='{$view->item->inputType}' name='opts[]' value='$key'> $val</label>\n";
                }
                ?>
            </div>
            <input type="hidden" name="a" value="run" />
            <input type="hidden" name="poll" value="<?php echo $view->item->pollId ?>" />
            <input type="hidden" name="item" value="<?php echo $view->item->id ?>" />
            <p><input type="submit" name="submit" value="<?php echo UI::$lang['next'] ?>" class="btn" /></p>
        </form>
