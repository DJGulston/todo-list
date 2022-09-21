create database if not exists example_db;

use example_db;

drop table if exists todos;

create table todos (
	id int auto_increment,
	title varchar(255) not null,
	created datetime default current_timestamp,
	completed int default 0,
	primary key (id)
);