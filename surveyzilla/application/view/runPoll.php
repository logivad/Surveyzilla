
            <h1><?php echo $view->item->pollName ?></h1>
            <form action="index.php" method="GET">
                <p><?php echo $view->item->questionText ?></p>
                <?php
                if (isset($view->item->imagePath)) {
                    echo "<p style='text-align: center;'><img src='{$view->item->imagePath}' /></p>\n";
                }
                if ('radio' === $view->item->inputType) {
                    foreach ($view->item->options as $key => $val) {
                        echo "<input type='radio' name='opts[]' value='$key'>$val<br />\n";
                    }
                }
                ?>
                <input type="hidden" name="a" value="run" />
                <input type="hidden" name="poll" value="<?php echo $view->item->pollId ?>" />
                <input type="hidden" name="item" value="<?php echo $view->item->id ?>" />
                <p><input type="submit" name="submit" value="Далее" /></p>
            </form>
