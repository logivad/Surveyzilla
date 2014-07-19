<?php
use surveyzilla\application\view\UI;
/* 
 * Displays graph for statistics
 * Requires that the View contains stat array with data
 */
?>
<div class="widget-graph">
    <table>
        <?php
        foreach ($view->stat as $q => $opts) {
            echo "<tr><td><p class='question'>$q</p></td><td></td>\t";
            foreach ($opts as $title => $count) {
                echo "<tr><td class='bars'><span class='option'>$title</span>"
                    . "<div class='bg' title='" . UI::$lang['total_votes'] . ": {$count['total']}'>"
                        . "<div class='graph-bar' style=\"width:{$count['percent']}%;\">"
                        . "</div>"
                    . "</div></td><td class='percentage'>{$count['percent']}%</td>\t";
            }
        }
        ?>
    </table>
</div>