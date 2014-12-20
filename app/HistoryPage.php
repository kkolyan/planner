<?

require_once 'mvc.php';

class HistoryPage extends UserPage {
    public $events;
    public $events_by_day;

    function __do_get() {
        parent::__do_get();

        if ($this->user) {

            $user_id = $this->user->id;

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


            $this->events_by_day = mapBy($this->events, function($i) {
                $d = new DateTime($i->at);
                return $d->format('d.m.Y');
            });
        }

        include "templates/history.php";
    }
}