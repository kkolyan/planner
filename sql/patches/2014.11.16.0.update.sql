
create table planner_user (
  id bigint auto_increment,
  `name` varchar(64) unique,
  pwd_hash text,
  primary key (id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;;

alter table planner_task add (user_id bigint);
alter table planner_task add constraint planner_task_user_id_fk foreign key (user_id) references planner_user(id);
