

alter table planner_task drop foreign key planner_task_user_id_fk;
alter table planner_task drop column user_id;
drop table planner_user;