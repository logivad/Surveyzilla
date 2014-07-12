<div class="run_poll">
    <div id="title">
        <?php echo $view->item->pollName ?>
    </div>
    <div class="content">
        <form action="index.php" method="GET">
            <p id="question"><?php echo $view->item->questionText ?></p>
            <?php
            if (isset($view->item->imagePath)) {
                echo "<p style='text-align: center;'><img src='{$view->item->imagePath}' /></p>\n";
            }
            foreach ($view->item->options as $key => $val) {
                echo "<label><input type='{$view->item->inputType}' name='opts[]' value='$key'>$val</label>\n";
            }
            ?>
            <input type="hidden" name="a" value="run" />
            <input type="hidden" name="poll" value="<?php echo $view->item->pollId ?>" />
            <p><input type="submit" name="submit" value="Далее" class="btn" /></p>
        </form>
    </div>
</div>