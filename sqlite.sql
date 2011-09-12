CREATE TABLE sessions (
  id varchar(255) PRIMARY KEY NOT NULL,
  data text NOT NULL,
  updated_on int(10) NOT NULL DEFAULT '0'
);

CREATE TABLE pages (
  id int(11) PRIMARY KEY NOT NULL,
  title varchar(65) NOT NULL DEFAULT '',
  slug varchar(20) UNIQUE NOT NULL DEFAULT '',
  data text NOT NULL,
  parent_id int(11) NOT NULL DEFAULT '',
  owner_id int(11) NOT NULL DEFAULT 1,
  perm varchar(3) NOT NULL DEFAULT '644'
);

CREATE TABLE blog (
  id int(11) PRIMARY KEY NOT NULL,
  title varchar(65) NOT NULL DEFAULT '',
  slug varchar(65) UNIQUE NOT NULL DEFAULT '',
  data text NOT NULL,
  pubDate datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
);

CREATE TABLE users (
  id int(11) PRIMARY KEY NOT NULL ,
  nid varchar(32) NOT NULL DEFAULT '',
  username varchar(65) UNIQUE NOT NULL DEFAULT '',
  password varchar(65) NOT NULL DEFAULT '',
  level varchar(10) NOT NULL DEFAULT 'user'
);

CREATE TABLE url_cache (
  url varchar(255) UNIQUE NOT NULL DEFAULT '',
  dt_refreshed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  dt_expires datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  data text NOT NULL
);
