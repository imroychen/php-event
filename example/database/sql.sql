-- mysql
 CREATE TABLE IF NOT EXISTS `ir_event_pool` (
  `id` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `starting_time` int(11) NOT NULL DEFAULT 0,
  `dependency` int(11) NOT NULL DEFAULT 0,
  `args` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `starting_time` (`starting_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Sqlite
create table ir_event_pool
(
    id            varchar(32)  not null primary key,
    name          varchar(100) not null,
    starting_time int(11) default 0 not null,
    dependency    int(11) default 0 not null,
    args           text         not null
);
create index starting_time  on ir_event_pool (starting_time);

-- 也可直接复制 data_store.sqlite.db; 文件