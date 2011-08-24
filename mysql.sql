CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `updated_on` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(65) NOT NULL DEFAULT '',
  `shortname` varchar(20) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `ownerid` int(11) NOT NULL,
  `perm` varchar(3) NOT NULL DEFAULT '644',
  PRIMARY KEY (`id`),
  UNIQUE KEY `shortname` (`shortname`)
);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(65) NOT NULL DEFAULT '',
  `password` varchar(65) NOT NULL DEFAULT '',
  `level` enum('user','admin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

CREATE TABLE `url_cache` (
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dt_refreshed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dt_expires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `url` (`url`)
);
