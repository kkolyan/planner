<html>
<? include '../www/head.php' ?>
<body onload="update_toggles()">
<? include 'menu.php' ?>
<?
/** @var $this Tasks */
?>
<?
if ($this->user) {
    ?><div class="task-tags-section"><?
        foreach ($this->tags as $tag => $freq) {
            ?><span id="tag-<?=esc($tag)?>" class="task-tag-visible" onclick="toggle_tag('<?=esc($tag)?>')"><?=esc($tag)?> (<?=$freq?>)</span>
            <script>register_tag('<?=esc($tag)?>')</script>
            <?
        }
    ?></div><?
    if ($this->categories) foreach ($this->categories as $cat) {
        ?><h4><span class="clickable" onclick="toggle('tasks<?=$cat->id?>')"><?= esc($cat->title) ?></span></h4>
        <div id="tasks<?=$cat->id?>">
            <div class="category-section">
                <form method="post" style="float: right">
                    <input type="hidden" name="method" value="add_task"/>
                    <input type="hidden" name="category_id" value="<?= esc($cat->id) ?>"/>
                    <label>
                        <textarea name="title" cols="40"></textarea>
                    </label>
                    <br/>
                    <input type="submit" value="Добавить Задачу"/>
                </form>
                <ul><?
                    $tasks = $this->tasks_by_category[$cat->id];
                    if ($tasks) {
                        foreach ($tasks as $task) {
                            ?><li title="<?=esc($task->title)?>" class="task-visible"><span><?= links(esc($task->title)) ?>
                                <span class="time clickable" onclick="toggle('task_controls<?=$task->id?>')">(<?=esc($task->opened_at)?>)</span></span>
                            <div id="task_controls<?=$task->id?>" class="state-hidden">
                                <div class="controlgroup">
                                    <form method="post">
                                        <input type="hidden" name="method" value="add_comment"/>
                                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                        <label>
                                            <textarea name="content"></textarea>
                                        </label>
                                        <input type="submit" value="Добавить Комментарий"/>
                                    </form>
                                </div>
                                <div class="controlgroup">
                                    <form method="post">
                                        <input type="hidden" name="method" value="move_task_up"/>
                                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                        <input type="submit" value="Поднять выше"/>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="method" value="move_task_down"/>
                                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                        <input type="submit" value="Опустить ниже"/>
                                    </form>
                                </div>
                                <div class="controlgroup">
                                    <form method="post">
                                        <input type="hidden" name="method" value="full_move_task_up"/>
                                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                        <input type="submit" value="В самый верх"/>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="method" value="full_move_task_down"/>
                                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                        <input type="submit" value="В самый низ"/>
                                    </form>
                                </div>
                                <div class="controlgroup">
                                    <?
                                    foreach ($this->categories as $innerCat) {
                                        if ($innerCat->id != $cat->id) {
                                            ?>
                                            <form method="post">
                                                <input type="hidden" name="category_id" value="<?=$innerCat->id?>"/>
                                                <input type="hidden" name="method" value="change_category"/>
                                                <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                                <input type="submit" value="В <?=esc($innerCat->title)?>"/>
                                            </form>
                                        <?
                                        }
                                    }
                                    ?>
                                </div class="controlgroup">
                            </div>
                            <?
                            if ($comments = $this->comments_by_task[$task->id]) {

                                ?><ul><?
                                foreach ($comments as $comment) {
                                    ?><li><pre style="display: inline"><?=links(esc($comment->content))?></pre> <span class="time">(<?=esc($comment->posted_at)?>)</span></li><?
                                }
                                ?></ul><?
                            }
                            ?>
                            </li><?
                        }
                    }
                    ?>
                </ul>
                <div style="clear: both;"></div>
            </div>
        </div>
    <?
    }
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