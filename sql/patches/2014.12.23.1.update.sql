
alter table planner_task add (notes text not null default '');


update planner_task t
  join (
         select task_id, group_concat(content separator '\n') notes
         from planner_task_comment
         group by task_id
       ) notes
    on t.id = notes.task_id
set t.notes = notes.notes;

drop table planner_task_comment;