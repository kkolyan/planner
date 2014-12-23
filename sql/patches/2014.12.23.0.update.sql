
create table planner_task_block (
  id bigint auto_increment,
  blocked_task_id bigint not null,
  blocking_task_id bigint not null,
  foreign key (blocked_task_id) references planner_task(id),
  foreign key (blocking_task_id) references planner_task(id),
  primary key (id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;