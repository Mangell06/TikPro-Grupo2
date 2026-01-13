-- eliminar primero si existen
drop database if exists simbiodb;

-- crear la base de datos
create database simbiodb
  character set utf8mb4
  collate utf8mb4_unicode_ci;

-- usar la base de datos
use simbiodb;

-- tabla users
create table if not exists users (
  id_user int auto_increment primary key,
  email varchar(255) not null unique,
  password char(64) not null,
  username varchar(100) not null,
  entity_name varchar(255) not null,
  entity_type enum('center', 'company') not null,
  logo_image varchar(255) not null,
  presentation text
);

-- tabla projects
create table if not exists projects (
  id_project int auto_increment primary key,
  title varchar(255) not null,
  description text not null,
  date_creation date not null,
  state enum('active', 'finished', 'archived') not null,
  id_owner int not null, 
  video varchar(255) not null,
  foreign key (id_owner) references users(id_user)
);

-- tabla messages
create table if not exists messages (
  id_message int auto_increment primary key,
  sender int not null,
  destination int not null,
  text_message text not null,
  date_message date not null,
  read_status boolean not null default false,
  foreign key (sender) references users(id_user),
  foreign key (destination) references users(id_user)
);

-- tabla categories
create table if not exists categories (
  id_category int auto_increment primary key,
  name_category varchar(255) not null,
  type enum('family', 'cicle') not null,
  id_category_parent int,
  foreign key (id_category_parent) references categories(id_category)
);

-- relación category_project
create table if not exists category_project (
  id_project int not null,
  id_category int not null,
  primary key (id_project, id_category),
  foreign key (id_project) references projects(id_project),
  foreign key (id_category) references categories(id_category)
);

-- relación category_user
create table if not exists category_user (
  id_user int not null,
  id_category int not null,
  primary key (id_user, id_category),
  foreign key (id_user) references users(id_user),
  foreign key (id_category) references categories(id_category)
);

-- tabla favorites
create table if not exists favorites (
  id_user int not null,
  id_project int not null,
  primary key (id_user, id_project),
  foreign key (id_user) references users(id_user),
  foreign key (id_project) references projects(id_project)
);

-- tabla likes
create table if not exists likes (
  id_user int not null,
  id_project int not null,
  primary key (id_user, id_project),
  foreign key (id_user) references users(id_user),
  foreign key (id_project) references projects(id_project)
);
