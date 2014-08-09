<div class="stat" <?php echo "data-sz-poll=\"{$view->pollId}\"" ?>>
<?php
use surveyzilla\application\view\UI;
echo '<h2>' . UI::$lang['stat_results'] . '</h2>';

foreach ($view->stat as $q => $opts) {
    echo "<h3>$q</h3>\t";
    echo "<table data-sz-q=\"$q\">";
    foreach ($opts as $title => $count) {
        $barTitle = UI::$lang['total_votes'];
        echo <<<TBL
    <tr>
        <td class="bars">
            <span>$title</span>
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