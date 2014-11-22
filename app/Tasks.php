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

    function add_task($params) {
        if ($user_id = $_SESSION['user_id']) {
            $task_id = insert("insert into planner_task (category_id, title, user_id) values ($params->category_id, '".esc_sql($params->title)."', $user_id)");
            $this->move_task($task_id, true);
        }
    }

    function add_comment($params) {
        if ($user_id = $_SESSION['user_id']) {
            insert("insert into planner_task_comment (task_id, content) values ($params->task_id, '".esc_sql($params->content)."')");
        }
    }

    function move_task_down($params) {
        if ($user_id = $_SESSION['user_id']) {
            $this->move_task($params->task_id, false);
        }
    }

    function move_task_up($params) {
        if ($user_id = $_SESSION['user_id']) {
            $this->move_task($params->task_id, true);
        }
    }

    function change_category($params) {
        if ($user_id = $_SESSION['user_id']) {
            $cat_ids = select("SELECT category_id FROM planner_task WHERE id = $params->task_id");
            $source_cat_id = $cat_ids[0]->category_id;
            update("UPDATE planner_task SET category_id = $params->category_id WHERE id = $params->task_id");
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

    private function move_task($task_id, $up) {
        $cmp = $up ? 'min' : 'max';
        $delta = $up ? -1 : 1;
        update("update planner_task a set a.`order` = (select b.x from (select $cmp(c.`order`) as x from planner_task c) b) + ($delta) where a.id = $task_id");
    }
}