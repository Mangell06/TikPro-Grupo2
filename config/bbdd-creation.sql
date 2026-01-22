-- eliminar primero si existen
drop database if exists simbiodb;

-- crear la base de datos
create database simbiodb
  character set utf8mb4
  collate utf8mb4_unicode_ci;

-- usar la base de datos
use simbiodb;

-- tabla users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  tfn VARCHAR(15) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  poblation VARCHAR(100) NOT NULL,
  entity_name VARCHAR(255) NOT NULL,
  entity_type ENUM('center', 'company') NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  code_activate VARCHAR(64),
  code_expire DATETIME,
  presentation TEXT
);

-- tabla projects
create table if not exists projects (
  id int auto_increment primary key,
  title varchar(255) not null,
  description text not null,
  date_creation date not null,
  state enum('active', 'finished', 'archived') not null,
  id_owner int not null, 
  video varchar(255) not null,
  logo_image varchar(255) not null,
  foreign key (id_owner) references users(id)
);

-- tabla messages
create table if not exists messages (
  id_message int auto_increment primary key,
  sender int not null,
  destination int not null,
  text_message text not null,
  date_message date not null,
  read_status boolean not null default false,
  foreign key (sender) references users(id),
  foreign key (destination) references users(id)
);

-- tabla categories
create table if not exists categories (
  id int auto_increment primary key,
  name varchar(255) not null,
  type enum('family', 'cicle') not null,
  id_category_parent int,
  foreign key (id_category_parent) references categories(id)
);

-- relación categories_project
create table if not exists categories_project (
  id_project int not null,
  id_category int not null,
  primary key (id_project, id_category),
  foreign key (id_project) references projects(id),
  foreign key (id_category) references categories(id)
);

-- relación categoies_user
create table if not exists categories_user (
  id_user int not null,
  id_category int not null,
  primary key (id_user, id_category),
  foreign key (id_user) references users(id),
  foreign key (id_category) references categories(id)
);

-- tabla favorites
create table if not exists favorites (
  id_user int not null,
  id_project int not null,
  primary key (id_user, id_project),
  foreign key (id_user) references users(id),
  foreign key (id_project) references projects(id)
);

-- tabla likes
create table if not exists likes (
  id_user int not null,
  id_project int not null,
  primary key (id_user, id_project),
  foreign key (id_user) references users(id),
  foreign key (id_project) references projects(id)
);
