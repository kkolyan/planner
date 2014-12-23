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
            ?><span id="tag-<?=esc($tag)?>" class="task-tag-visible" onclick="toggle_tag('<?=esc($tag)?>')" ondblclick="toggle_tags_except('<?=esc($tag)?>')"><?=esc($tag)?> (<?=$freq?>)</span>
            <script>register_tag('<?=esc($tag)?>')</script>
            <?
        }
        $hint = 'Для создания тегов используйте квадратные скобки. например: [работа] распечатать документы';
    ?>
    <a title="<?=$hint?>" href="javascript:alert('<?=$hint?>')"><b> ? </b></a>
    </div><?
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
                                <pre style="margin-left: 50px"><?=links(esc($task->notes))?></pre>
                                <div class="controlgroup">
                                    <form method="post">
                                        <input type="hidden" name="method" value="update_notes"/>
                                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                                        <label>
                                            <textarea name="content"><?=esc($task->notes)?></textarea>
                                        </label>
                                        <input type="submit" value="Обновить заметки"/>
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
}
?>
</body>
</html>