<?

require_once 'mvc.php';

ensure_backup('Y.m.d');

class TasksTreePage extends UserPage {
    public $categories;
    public $tasks;
    public $blocks;
    public $tasks_by_category;
    public $tags;
    public $blocked_by_task;
    public $blocking_by_task;
    public $tasks_by_id;

    function add_task($params) {
        if ($user_id = $_SESSION['user_id']) {
            $smallest_order = selectCell("select min(`order`) from planner_task where category_id = $params->category_id");
            $smallest_order = intval($smallest_order);
            $task_id = insert("insert into planner_task (category_id, title, `order`, user_id) values ($params->category_id, '".esc_sql($params->title)."',$smallest_order - 1, $user_id)");
        }
    }

    function update_notes($params) {
        if ($user_id = $_SESSION['user_id']) {
            update("update planner_task set notes = '".esc_sql($params->content)."' where id = ".intval($params->task_id));
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

            $user_id = $this->user->id;

            $this->categories = select('select * from planner_category order by `order` asc');
            $this->tasks = select("select * from planner_task where user_id = $user_id order by `order` asc");
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
            foreach ($this->tasks as $task) {
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

            $this->tasks_by_category = mapBy($this->tasks, function($i) {
                return $i->category_id;
            });

            $this->tasks_by_id = array();
            foreach ($this->tasks as $task) {
                $this->tasks_by_id[$task->id] = $task;
            }
        }

        include "templates/tasks_tree.php";
    }

    public function is_of_category($task_id, $category_id) {
        $blockers = $this->blocking_by_task[$task_id];
        if ($blockers and count($blockers) > 0) {
            foreach ($blockers as $blocker) {
                if ($this->is_of_category($blocker, $category_id)) {
                    return true;
                }
            }
            return false;
        }
        $task = $this->tasks_by_id[$task_id];
        if ($task->category_id == $category_id) {
            return true;
        }
        return false;
    }

    private function full_move_task($task_id, $up) {
        $cmp = $up ? 'min' : 'max';
        $delta = $up ? -1 : 1;
        update("update planner_task a set a.`order` = (select b.x from (select $cmp(c.`order`) as x from planner_task c) b) + ($delta) where a.id = $task_id");
    }
}