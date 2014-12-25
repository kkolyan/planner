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
function render_task($page, $task, $path, $parent_id) {
    if (!$task) {
        throw new Exception();
    }
    if (!$page->is_of_category($task->id)) {
        return;
    }
    $blocked = $page->blocking_by_task[$task->id] and count($page->blocking_by_task[$task->id]) > 0;

    ?><li title="{id=<?=esc($task->id)?>}<?=esc($task->title)?>" class="task-visible">
    <?
    if (is_string($task->deferred_by)) {
        ?><span class="deferred_by"><?=$task->deferred_by?></span><?
    }
    ?>
    <span class="<?= $blocked ? 'blocked-task' : 'non-blocked-task'?>"><?= links(esc($task->title)) ?>
        <span class="time">(<?=esc($task->opened_at)?>)</span></span>
    <span class="clickable" onclick="toggle('task_controls<?=$path.'_'.$task->id?>')" style="flo1at: left;margin: -10px 0; padding: 0px 0;">. . .</span>
        <?
        if ($task->notes) {
            ?><pre class="notes"><?=links(esc($task->notes))?></pre><?
        }
        ?>
        <div id="task_controls<?=$path.'_'.$task->id?>" class="state-hidden">
            <div class="task_content">
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
                if ($page->deferred) {
                    ?>
                    <form method="post">
                        <input type="hidden" name="method" value="resume"/>
                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                        <input type="submit" value="Возобновить"/>
                    </form>
                <?
                } else {
                    ?>
                    <form method="post">
                        <input type="hidden" name="method" value="defer"/>
                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                        <input name="deferred_by" value="Какая-то причина"/>
                        <input type="submit" value="Отложить"/>
                    </form>
                <?
                }
                ?>
            </div>
            <div class="controlgroup">
                <br/>
                <form method="post">
                    <input type="hidden" name="method" value="close"/>
                    <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                    <input type="submit" value="Закрыть"/>
                </form>
            </div>
            <br/>
            <div class="controlgroup">
                <form method="post">
                    <input type="hidden" name="method" value="add_blocker"/>
                    <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                    <select name="blocking_task_id">
                        <?
                        foreach ($page->opened_tasks as $blocking_task_candidate) {
                            if ($blocking_task_candidate->id == $task->id) {
                                continue;
                            }
                            if ($page->is_blocked_by($task->id,$blocking_task_candidate->id)) {
                                continue;
                            }
                            if ($page->is_blocked_by($blocking_task_candidate->id, $task->id)) {
                                continue;
                            }
                            if ($blockers = $page->blocking_by_task[$task->id]) {
                                if (in_array($blocking_task_candidate->id, $blockers)) {
                                    continue;
                                }
                            }
                            $deferred = is_string($blocking_task_candidate->deferred_by);
                            $candidate_category = $deferred ? 1 : 0;
                            ?><option class="cite<?=$candidate_category?>" value="<?=$blocking_task_candidate->id?>"><?=esc($blocking_task_candidate->title)?> (<?=$deferred ? 'Отложено' : 'Активно'?>)</option><?
                        }
                        ?>
                    </select>
                    <input type="submit" value="Добавить блокирующую задачу"/>
                </form>
            </div>
            <div class="controlgroup">
                <br/>
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
            if ($blocker) {
                render_task($page, $blocker, $path.'_'.$task->id, $task->id);
            }
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
    </div>

    <br/>
    <form method="post" style="float: right">
        <input type="hidden" name="method" value="add_task"/>
        <input type="hidden" name="category_id" value="<?= esc($cat->id) ?>"/>
        <label>
            <textarea name="title" cols="40"></textarea>
        </label>
        <br/>
        <input type="submit" value="Добавить Задачу"/>
    </form>
    <?
    $cur_count = count($this->tasks);
    $alt_count = count($this->opened_tasks) - count($this->tasks);
    if ($this->deferred) {
        ?><a href="?mode=active">Активные (<?=$alt_count?>)</a> <b>Отложенные (<?=$cur_count?>)</b><?
    } else {
        ?><b>Активные (<?=$cur_count?>)</b> <a href="?mode=deferred">Отложенные (<?=$alt_count?>)</a><?
    }
    ?>
    <ul><?
        foreach ($this->opened_tasks as $task) {
            if ($blocked = $this->blocked_by_task[$task->id]) {
                foreach ($blocked as $b) {
                    if ($this->is_of_category($b)) {
                        continue 2;
                    }
                }
            }
            render_task($this, $task, '', null);
        }
        ?>
    </ul>
    <?
}
?>
</body>
</html>