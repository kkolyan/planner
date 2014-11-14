<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf8"/>
    <script type="text/javascript">
        function toggle(q) {
            var o = document.getElementById(q);
            if (o.className == 'state-hidden') {
                o.className = 'state-shown';
            } else {
                o.className = 'state-hidden';
            }
        }
    </script>
    <style type="text/css">
        .state-hidden {
            display: none;
        }

        .clickable {
            cursor: pointer;
        }

        .time {
            color: #ccc;
        }
        form {
            display: inline;
        }

        .linkbutton {
            border: none;
            background: none;
            cursor: pointer;
        }
        .linkbutton:hover, .clickable:hover {
            text-decoration: underline;
        }

        .controlgroup {
            /*border-left: 3px solid #484848;*/
            height: 30px;
            padding-left: 20px;
            margin-left: 10px;
            display: inline;
        }

        .cite1 {
            color: blue;
        }
        .cite2 {
            color: #730000;
        }
        .cite3 {
            color: #018100;
        }
        .cite4 {
            color: #c39165;
        }
    </style>
</head>
<body>
<?


if (false) {
    ?><pre><?
    var_dump(get_defined_vars());
    ?></pre><?
}
?>
<?
if ($categories) foreach ($categories as $cat) {
    ?><h4 class="clickable" onclick="toggle('add_task<?=$cat->id?>')"><?= esc($cat->title) ?></h4><ul><?
    $tasks = $tasks_by_category[$cat->id];
    if ($tasks) {
        foreach ($tasks as $task) {
            ?><li><span class="clickable" onclick="toggle('task_controls<?=$task->id?>')"><?= esc($task->title) ?>
                <span class="time">(<?=esc($task->opened_at)?>)</span></span>
            <?
            if ($comments = $comments_by_task[$task->id]) {

                ?><ul><?
                foreach ($comments as $comment) {
                    ?><li><?=esc($comment->content)?> <span class="time">(<?=esc($comment->posted_at)?>)</span></li><?
                }
                ?></ul><?
            }
            ?>
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
                        <input type="submit" value="Поднять"/>
                    </form>
                    <form method="post">
                        <input type="hidden" name="method" value="move_task_down"/>
                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                        <input type="submit" value="Спустить"/>
                    </form>
                </div>
                <div class="controlgroup">
                    <form method="post">
                        <input type="hidden" name="method" value="change_category"/>
                        <input type="hidden" name="task_id" value="<?= esc($task->id) ?>"/>
                        <label>
                            <select name="category_id">
                                <?
                                foreach ($categories as $innerCat) {
                                    if ($innerCat->id != $cat->id) {
                                        ?><option value="<?=$innerCat->id?>"><?=$innerCat->title?></option><?
                                    }
                                }
                                ?>
                            </select>
                        </label>
                        <input type="submit" value="Сменить Категорию"/>
                    </form>
                </div class="controlgroup">
            </div>
            </li><?
        }
    }
    ?>
    </ul>
    <form id="add_task<?=$cat->id?>" method="post" class="state-hidden">
        <input type="hidden" name="method" value="add_task"/>
        <input type="hidden" name="category_id" value="<?= esc($cat->id) ?>"/>
        <label>
            <textarea name="title"></textarea>
        </label>
        <input type="submit" value="Добавить Задачу"/>
    </form>
<?
}
?>
<h4>История</h4>
<ul>
<?
if ($events) foreach ($events as $event) {
    $s = esc($event->f);
    for ($i = 1; $i <= 4; $i ++) {
        $var = "a$i";
        $val = esc($event->$var);
        $s = str_replace("%$i", "<span class='cite$i'>$val</span>", $s);
    }
    ?><li><span class="time"> <?=$event->at?></span><?= $s ?></li><?
}
?>
</ul>
</body>
</html>