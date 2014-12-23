
create table planner_change_event (
  id bigint auto_increment,
  item_id bigint not null,
  item_type varchar(64) not null,
  old_value text not null,
  new_value text not null,
  `at` timestamp default now(),
  primary key (id)
);