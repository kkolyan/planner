<?

require_once 'mvc.php';

ensure_backup('Y.m.d');

class TasksTreePage extends UserPage {
    public $tasks;
    public $opened_tasks;
    public $blocks;
    public $tags;
    public $blocked_by_task;
    public $blocking_by_task;
    public $tasks_by_id;

    public $deferred;

    function close($params) {
        if ($user_id = $_SESSION['user_id']) {
            update("
                update planner_task
                set closed_at = now()
                where id = ".intval($params->task_id));
        }
    }

    function defer($params) {
        if ($user_id = $_SESSION['user_id']) {
            update("
                update planner_task
                set deferred_by = '".esc_sql($params->deferred_by)."'
                where id = ".intval($params->task_id));
            insert("insert into planner_change_event
                (item_id, item_type, old_value, new_value) values
                (".intval($params->task_id).", 'task.defer', null, '".esc_sql($params->deferred_by)."')");
        }
    }

    function resume($params) {
        if ($user_id = $_SESSION['user_id']) {
            update("
                update planner_task
                set deferred_by = null
                where id = ".intval($params->task_id));
            insert("insert into planner_change_event
                (item_id, item_type, old_value, new_value) values
                (".intval($params->task_id).", 'task.resume', null, null)");
        }
    }

    function add_task($params) {
        if ($user_id = $_SESSION['user_id']) {
            $smallest_order = selectCell("
                select min(`order`) from planner_task
                where not closed_at");
            $smallest_order = intval($smallest_order);
            $task_id = insert("insert into planner_task (title, `order`, user_id) values ('".esc_sql($params->title)."',$smallest_order - 1, $user_id)");
        }
    }

    function update_title($params) {
        if ($user_id = $_SESSION['user_id']) {
            $old_value = selectCell('select title from planner_task where id = '.intval($params->task_id));
            update("update planner_task set title = '".esc_sql($params->title)."' where id = ".intval($params->task_id));
            insert("insert into planner_change_event
                (item_id, item_type, old_value, new_value) values
                (".intval($params->task_id).", 'task.title', '".esc_sql($old_value)."', '".esc_sql($params->title)."')");
        }
    }

    function update_notes($params) {
        if ($user_id = $_SESSION['user_id']) {
            $old_value = selectCell('select notes from planner_task where id = '.intval($params->task_id));
            if (!$old_value) {
                $old_value = 'NULL';
            }
            update("update planner_task set notes = '".esc_sql($params->content)."' where id = ".intval($params->task_id));
            insert("insert into planner_change_event
                (item_id, item_type, old_value, new_value) values
                (".intval($params->task_id).", 'task.notes', '".esc_sql($old_value)."', '".esc_sql($params->content)."')");
        }
    }

    function full_move_task_down($params) {
        if ($user_id = $_SESSION['user_id']) {
            $this->full_move_task($params->task_id, false);
        }
    }

    function full_move_task_up($params) {
        if ($user_id = $_SESSION['user_id']) {
            $this->full_move_task($params->task_id, true);
        }
    }

    function move_task_up($params) {
        $this->move_task($params, true);
    }

    function move_task_down($params) {
        $this->move_task($params, false);
    }

    function move_task($params,$up) {
        if ($user_id = $_SESSION['user_id']) {

            $task = selectRow("select id,`order`, category_id from planner_task where user_id = $user_id and id = $params->task_id__sql");
            if (!$task) {
                return;
            }

            $cmp = $up ? '<' : '>';
            $dir = $up ? 'desc' : 'asc';

            $neighbour = selectRow("
                select id, `order`
                from planner_task
                where user_id = $user_id
                and category_id = $task->category_id
                and `order` $cmp $task->order
                order by `order` $dir
                limit 1
            ");

            if ($neighbour) {
                update("update planner_task set `order` = $neighbour->order where id = $task->id");
                update("update planner_task set `order` = $task->order where id = $neighbour->id");
            }
        }
    }

    function change_category($params) {
        if ($user_id = $_SESSION['user_id']) {
            $cat_ids = select("SELECT category_id FROM planner_task WHERE id = $params->task_id");
            $source_cat_id = $cat_ids[0]->category_id;
            $smallest_order = selectCell("select min(`order`) from planner_task where category_id = $params->category_id");
            $smallest_order = intval($smallest_order);
            update("
                UPDATE planner_task
                SET category_id = $params->category_id,
                `order` = $smallest_order - 1
                WHERE id = $params->task_id
            ");
            insert("INSERT INTO planner_task_category_change (task_id, source_category_id, target_category_id) VALUES ($params->task_id, $source_cat_id, $params->category_id)");
        }
    }

    function add_blocker($params) {
        if ($user_id = $_SESSION['user_id']) {
            insert('insert into planner_task_block (blocked_task_id, blocking_task_id) values ('.intval($params->task_id).', '.intval($params->blocking_task_id).')');
        }
    }

    function remove_blocker($params) {
        if ($user_id = $_SESSION['user_id']) {
            update('delete from planner_task_block where blocked_task_id = '.intval($params->task_id).' and blocking_task_id = '.intval($params->blocking_task_id));
        }
    }

    function __do_get() {
        parent::__do_get();

        if ($this->user) {

            $this->deferred = $this->mode == 'deferred';

            $user_id = $this->user->id;

            $this->tasks = select("
                select * from planner_task
                where user_id = $user_id
                and not closed_at
                and deferred_by is ".($this->deferred ? 'not' : '')." null
                order by `order` asc");

            $this->opened_tasks = select("
                select * from planner_task
                where user_id = $user_id
                and not closed_at
                order by opened_at desc");

            $this->blocks = select("
                select distinct b.blocked_task_id, b.blocking_task_id
                from planner_task_block b
                inner join planner_task t
                on t.id = b.blocked_task_id
                or t.id = b.blocking_task_id
                where t.user_id = $user_id");

            $this->blocking_by_task = mapBy($this->blocks, function($i) { return $i->blocked_task_id; }, function($i) { return $i->blocking_task_id; });
            $this->blocked_by_task = mapBy($this->blocks, function($i) { return $i->blocking_task_id; }, function($i) { return $i->blocked_task_id; });

            $this->tags = array();
            foreach ($this->opened_tasks as $task) {
                preg_match_all('/\[([A-z0-9А-я ]*?)\]/u',$task->title, $matches);
                if (!$matches[1]) {
                    $matches[1] = array('');
                }
                foreach ($matches[1] as $tag) {
                    $c = $this->tags[$tag];
                    if (!$c) {
                        $c = 0;
                    }
                    $this->tags[$tag] = $c + 1;
                }
            }

            $this->tasks_by_id = array();
            foreach ($this->opened_tasks as $task) {
                $this->tasks_by_id[$task->id] = $task;
            }
        }

        include "templates/tasks_tree.php";
    }

    public function is_of_category($task_id) {
        $blockers = $this->blocking_by_task[$task_id];
        if ($blockers and count($blockers) > 0) {
            foreach ($blockers as $blocker) {
                if ($this->is_of_category($blocker)) {
                    return true;
                }
            }
            return false;
        }
        $task = $this->tasks_by_id[$task_id];
        if ($task->deferred_by ? $this->deferred : !$this->deferred) {
            return true;
        }
        return false;
    }

    public function is_blocked_by($task_id, $by_id) {
        if ($blocked_by_ids = $this->blocked_by_task[$by_id]) {
            if (in_array($task_id, $blocked_by_ids)) {
                return true;
            }
            foreach ($blocked_by_ids as $blocked_by_id) {
                if ($this->is_blocked_by($task_id, $blocked_by_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function full_move_task($task_id, $up) {
        $cmp = $up ? 'min' : 'max';
        $delta = $up ? -1 : 1;
        update("update planner_task a set a.`order` = (select b.x from (select $cmp(c.`order`) as x from planner_task c) b) + ($delta) where a.id = $task_id");
    }
}