<?php
use surveyzilla\application\view\UI;
/* 
 * Displays graph for statistics
 * Requires that the View contains stat array with data
 */
?>
<div class="widget-graph">
        <?php
        foreach ($view->stat as $q => $opts) {
            echo "<p class='question'>$q</p>\t";
            echo "<table data-sz-q=\"$q\">";
            foreach ($opts as $title => $count) {
                $barTitle = UI::$lang['total_votes'];
                echo <<<TBL
    <tr>
        <td class="bars">
            <span class='option'>$title</span>
            <div class="bg" title="$barTitle: {$count->total}">
                <div class="graph-bar" data-sz-option="$title" style="width:{$count->percent}%;"></div>
            </div>
        </td>
        <td class="percentage" data-sz-option="$title">{$count->percent}%</td>
    </tr>
TBL;
            }
            echo '</table>';
        }
        ?>
</div>