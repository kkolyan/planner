
alter table planner_user add (admin char(1));

create table planner_invite (
  `key` varchar(128) unique,
  created_at timestamp default now()
)
engine = InnoDB
default character set = utf8
collate = utf8_bin;