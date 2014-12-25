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
                        and closed_at is not null
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
                    union
                        select at, 'Заголовок задачи %1 (%2) заменен на %3' f,
                            old_value a1,
                            item_id a2,
                            new_value a3,
                            null a4
                        from planner_change_event
                        where item_type = 'task.title'
                    union
                        select at, 'Заметки задачи %1 (%2) заменены с %3 на %4' f,
                            (select title from planner_task where id = item_id) a1,
                            item_id a2,
                            old_value a3,
                            new_value a4
                        from planner_change_event
                        where item_type = 'task.notes'
                    union
                        select at, 'Задача %1 (%2) отложена по причине: %3' f,
                            (select title from planner_task where id = item_id) a1,
                            item_id a2,
                            new_value a3,
                            null a4
                        from planner_change_event
                        where item_type = 'task.defer'
                    union
                        select at, 'Задача %1 (%2) возобновлена' f,
                            (select title from planner_task where id = item_id) a1,
                            item_id a2,
                            null a3,
                            null a4
                        from planner_change_event
                        where item_type = 'task.resume'
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