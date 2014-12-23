<html>
<? include '../www/head.php' ?>
<body onload="update_toggles()">
<? include 'menu.php' ?>
<?
/** @var $this TasksTreePage */
?>
<?

$counter = 0;

function incrementAndGet() {
    global $counter;
    $counter ++;
    return $counter;
}

/**
 * @param TasksTreePage $page
 * @param $cat
 * @param $task
 * @param $parent_id
 */
function render_task($page, $cat, $task, $path, $parent_id) {
    if (!$page->is_of_category($task->id, $cat->id)) {
        return;
    }
    $blocked = $page->blocking_by_task[$task->id] and count($page->blocking_by_task[$task->id]) > 0;

    ?><li title="<?=esc($task->title)?>" class="task-visible"><span class="<?= $blocked ? 'blocked-task' : 'non-blocked-task'?>"><?= links(esc($task->title)) ?>
        <span class="time clickable" onclick="toggle('task_controls<?=$path.'_'.$task->id?>')">(<?=esc($task->opened_at)?>)</span></span>
    <div id="task_controls<?=$path.'_'.$task->id?>" class="state-hidden">
        <div class="task_content">
            <pre><?=links(esc($task->notes))?></pre>
            <div class="controlgroup">
                <form method="post">
                    <input type="hidden" name="method" value="update_title"/>
                    <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                    <label>
                        <input name="title" size="<?=mb_strlen($task->title, 'utf-8')?>" value="<?=esc($task->title)?>"/>
                    </label>
                    <input type="submit" value="Обновить заголовок"/>
                </form>
            </div>
            <br/>
            <div class="controlgroup">
                <form method="post">
                    <input type="hidden" name="method" value="update_notes"/>
                    <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                    <label>
                        <textarea rows="<?=count(preg_split('/\n/', $task->notes))?>" cols="<?=max_sub_line($task->notes)?>" name="content"><?=esc($task->notes)?></textarea>
                    </label>
                    <input type="submit" value="Обновить заметки"/>
                </form>
            </div>
            <br/>
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
            <br/>
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
            <br/>
            <div class="controlgroup">
                <?
                foreach ($page->categories as $innerCat) {
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
            </div>
            <br/>
            <div class="controlgroup">
                <form method="post">
                    <input type="hidden" name="method" value="add_blocker"/>
                    <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                    <select name="blocking_task_id">
                        <?
                        foreach ($page->tasks as $blocking_task_candidate) {
                            if ($blockers = $page->blocking_by_task[$task->id]) {
                                if (in_array($blocking_task_candidate->id, $blockers)) {
                                    continue;
                                }
                            }
                            $candidate_category = $page->category_by_id[$blocking_task_candidate->category_id];
                            ?><option class="cite<?=$candidate_category->id?>" value="<?=$blocking_task_candidate->id?>"><?=esc($blocking_task_candidate->title)?> (<?=$candidate_category->title?>)</option><?
                        }
                        ?>
                    </select>
                    <input type="submit" value="Добавить блокирующую задачу"/>
                </form>
            </div>
            <div class="controlgroup">
                <?
                if ($parent_id) {
                    ?>
                    <form method="post">
                        <input type="hidden" name="method" value="remove_blocker"/>
                        <input type="hidden" name="task_id" value="<?= esc($parent_id) ?>"/>
                        <input type="hidden" name="blocking_task_id" value="<?= esc($task->id) ?>"/>
                        <input type="submit" value="Убрать из блокирующих"/>
                    </form>
                <?
                }
                ?>
            </div>
        </div>
    </div>
    <?
    if ($blockers = $page->blocking_by_task[$task->id]) {
        ?><ul><?
        foreach ($blockers as $blocker_id) {
            $blocker = $page->tasks_by_id[$blocker_id];
            render_task($page, $cat, $blocker, $path.'_'.$task->id, $task->id);
        }
        ?></ul><?
    }
    ?>
    </li><?

};

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
                    $tasks = $this->tasks;
                        //$this->tasks_by_category[$cat->id];
                    if ($tasks) {
                        foreach ($tasks as $task) {
                            if ($blocked = $this->blocked_by_task[$task->id]) {
                                foreach ($blocked as $b) {
                                    if ($this->is_of_category($b, $cat->id)) {
                                        continue 2;
                                    }
                                }
                            }
                            render_task($this, $cat, $task, '', null);
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