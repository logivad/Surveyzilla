<pre>
<?php if ($view->poll === false){ ?>
+-------------------+
|  Poll not found!  |
+-------------------+
<?php } else {
    echo "+--------------------------------------+\n";
    echo '| '.$view->poll->getName();
    for ($i=36-strlen($view->poll->getName()); $i>0; $i--){
        echo ' ';
    }
    echo " |\n+--------------------------------------+\n";
    for ($i=0, $size=$view->poll->getSize(); $i<$size; $i++){
        echo "\n".$view->poll->getItem($i)->getQuestion()."\n\n";
        $options = $view->poll->getItem($i)->getOptions()->getOptionList();
        for ($j=0, $size2=sizeof($options); $j<$size2; $j++){
            echo "\t{$options[$j]}\n";
        }
        if ($view->poll->getItem($i)->getOptions()->customFieldAllowed()){
            echo "\tother:________\n";
        }
    }
}
?>
</pre>