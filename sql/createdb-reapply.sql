
drop table if exists planner_task_category_change;
drop table if exists planner_category_change_schedule;
drop table if exists planner_task_comment;
drop table if exists planner_task;
drop table if exists planner_category;

drop table if exists planner_db_patch;

create table planner_db_patch (
  id text,
  `at` timestamp
);

create table planner_category (
  id bigint,
  title text,
  `order` int,
  primary key (id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;

create table planner_task (
  id bigint auto_increment,
  title text not null,
  opened_at timestamp default now(),
  closed_at timestamp,
  closed bool default 0,
  canceled bool default 0,
  category_id bigint,
  `order` int default 0,
  primary key (id),
  foreign key (category_id) references planner_category(id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;

create table planner_task_comment (
  id bigint auto_increment,
  task_id bigint not null,
  content text not null,
  posted_at timestamp default now(),
  primary key (id),
  foreign key (task_id) references planner_task(id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;

create table planner_category_change_schedule (
  id bigint auto_increment,
  task_id bigint not null,
  category_id bigint not null,
  at timestamp not null,
  reason text,
  primary key (id),
  foreign key (task_id) references planner_task(id),
  foreign key (category_id) references planner_category(id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;

create table planner_task_category_change (
  id bigint auto_increment,
  changed_at timestamp default now(),
  task_id bigint not null,
  source_category_id bigint not null,
  target_category_id bigint not null,
  primary key (id),
  foreign key (task_id) references planner_task(id),
  foreign key (source_category_id) references planner_category(id),
  foreign key (target_category_id) references planner_category(id)
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;

insert into planner_category
  (id, title, `order`) values
  (1, 'Активные',1),
  (2, 'Пассивные', 2),
  (3, 'Висящие', 3),
  (4, 'Закрытые', 4);
