<div class="content">
    <div class="msg">
        <?php
        echo $view->message;
        if (isset($view->errorCode)) {
            echo "<p class='error-code'>({$view->errorCode})</p>";
        }
        ?>
    </div>
</div>