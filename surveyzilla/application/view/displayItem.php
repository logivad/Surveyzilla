<div id="border">
    <h2><?= $view->item->getQuestion() ?></h2>
    <form action="index.php?action=run" method="POST">
        <?php
        use surveyzilla\application\model\poll\Options,
            surveyzilla\application\model\UI;
        $optionList = $view->item->getOptions()->getOptionList();
        switch ($view->item->getOptions()->getType()){
            case Options::TYPE_RADIO:
                $inputType = 'radio';
                break;
            case Options::TYPE_CHECKBOX:
                $inputType = 'checkbox';
                break;
        }
        for ($i = 0, $size= sizeof($optionList); $i < $size; $i++){
            echo "\n\t<input type=\"$inputType\" name=\"options[]\" value=\"$i\">$optionList[$i]<br />";
        }
        if ($view->item->getOptions()->customFieldAllowed()){
            echo "\n\t<input type=\"text\" name=\"customOption\" placeholder=\"".UI::$text['enterCustOp'].'"><br />';
        }
        echo "\n\t<input type=\"hidden\" name=\"pollId\" value=\"{$view->pollId}\">",
             "\n\t<input type=\"hidden\" name=\"itemId\" value=\"{$view->item->getId()}\">",
             "\n\t<input type=\"submit\" name=\"submit\">\n";
        ?>
    </form>
</div>