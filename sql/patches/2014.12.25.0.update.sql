
update planner_task t
  join (
         select max(changed_at) changed_at, task_id
         from planner_task_category_change
         group by task_id
       ) c
    on t.id = c.task_id
set t.closed_at = c.changed_at
where t.category_id = 4;

alter table planner_task drop column closed;
alter table planner_task drop column canceled;

alter table planner_task add column deferred_by text;

update planner_task set deferred_by = 'as passive' where category_id = 2;
update planner_task set deferred_by = 'as suspended' where category_id = 3;

alter table planner_change_event modify column old_value text;
alter table planner_change_event modify column new_value text;