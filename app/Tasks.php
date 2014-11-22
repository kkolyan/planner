<?

require_once 'mvc.php';

ensure_backup('Y.m.d');

class Tasks extends UserPage {
    public $categories;
    public $tasks;
    public $comments;
    public $events;
    public $tasks_by_category;
    public $comments_by_task;
    public $events_by_day;
    public $tags;

    function add_task($params) {
        if ($user_id = $_SESSION['user_id']) {
            $task_id = insert("insert into planner_task (category_id, title, `order`, user_id) values ($params->category_id, '".esc_sql($params->title)."',(select min(`order`) from planner_task where category_id = $params->category_id) - 1, $user_id)");
        }
    }

    function add_comment($params) {
        if ($user_id = $_SESSION['user_id']) {
            insert("insert into planner_task_comment (task_id, content) values ($params->task_id, '".esc_sql($params->content)."')");
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

            update("update planner_task set `order` = $neighbour->order where id = $task->id");
            update("update planner_task set `order` = $task->order where id = $neighbour->id");
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

    function __do_get() {
        parent::__do_get();

        if ($this->user) {

            $user_id = $this->user->id;

            $this->categories = select('select * from planner_category order by `order` asc');
            $this->tasks = select("select * from planner_task where user_id = $user_id order by `order` asc");
            $this->comments = select('select * from planner_task_comment order by posted_at desc');
            $this->events = select("
                select * from (
                    select opened_at `at`, 'Открыта задача %1 (%2)' f, title a1, id a2, null a3, null a4
                        from planner_task where user_id = $user_id
                    union
                        select closed_at `at`, 'Закрыта задача %1 (%2)' f, title a1, id a2, null a3, null a4
                        from planner_task
                        where user_id = $user_id
                        and closed_at <> null
                    union
                        select posted_at `at`,
                            'Комментарий к задаче %1 (%2): %3' f,
                            (select title from planner_task where id = task_id) a1,
                            task_id a2,
                            content a3, null a4
                        from planner_task_comment
                        where task_id in (select id from planner_task where user_id = $user_id)
                    union
                        select changed_at `at`, 'Категория задачи %1 (%2) сменена с %3 на %4' f,
                            (select title from planner_task where id = task_id) a1, task_id a2,
                            (select title from planner_category where id = source_category_id) a3,
                            (select title from planner_category where id = target_category_id) a4
                        from planner_task_category_change
                        where task_id in (select id from planner_task where user_id = $user_id)
                ) s order by s.`at` desc
            ");

            $this->tags = array();
            foreach ($this->tasks as $task) {
                preg_match_all('/\[([A-z0-9 ]*?)\]/',$task->title, $matches);
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
            $this->comments_by_task = mapBy($this->comments, function($i) {
                return $i->task_id;
            });
            $this->events_by_day = mapBy($this->events, function($i) {
                $d = new DateTime($i->at);
                return $d->format('d.m.Y');
            });
        }

        include "templates/tasks.php";
    }

    private function full_move_task($task_id, $up) {
        $cmp = $up ? 'min' : 'max';
        $delta = $up ? -1 : 1;
        update("update planner_task a set a.`order` = (select b.x from (select $cmp(c.`order`) as x from planner_task c) b) + ($delta) where a.id = $task_id");
    }
}