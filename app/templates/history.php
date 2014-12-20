<html>
<? include '../www/head.php' ?>
<body onload="update_toggles()">
<? include 'menu.php' ?>
<?
/** @var $this HistoryPage */
?>
<?
if ($this->user) {
    ?>
    <h4>История</h4>
    <?
    foreach ($this->events_by_day as $day => $events) {

        ?>
        <div>
        <span class="clickable" onclick="toggle('history<?=$day?>')"><?=$day?></span><?
        ?><ul id="history<?=$day?>"><?
            foreach ($events as $event) {
                $s = esc($event->f);
                for ($i = 1; $i <= 4; $i ++) {
                    $var = "a$i";
                    $val = esc($event->$var);
                    $s = str_replace("%$i", "<span class='cite$i'>$val</span>", $s);
                }
                ?><li><span class="time"><?= format_date($event->at, 'H:i:s') ?></span> <?= $s ?></li><?
            }
            ?></ul>
        </div><?
    }
}
?>
</body>
</html>