create table users (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username varchar(50) not null unique,
	password varchar(50)
);

create table houses (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name varchar(50) not null
);

create table tasks (
	id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	description varchar(50) not null,
	user_id int unsigned,
	foreign key (user_id) references users(id)
);

create table houses_users (
	id INT(10) NOT NULL AUTO_INCREMENT,
	user_id int(10) not null,
	house_id int(10) not null,
	primary key(id)
);

