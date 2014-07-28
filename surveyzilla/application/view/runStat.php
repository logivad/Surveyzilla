<div class="stat" <?php echo "data-sz-poll=\"{$view->pollId}\"" ?>>
    <?php
    use surveyzilla\application\view\UI;
    echo '<h2>' . UI::$lang['stat_results'] . '</h2>';
    require_once 'surveyzilla/application/view/widget/graph.php';
    ?>
</div>