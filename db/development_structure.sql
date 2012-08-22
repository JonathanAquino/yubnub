CREATE TABLE `banned_url_patterns` (
  `id` int(11) NOT NULL auto_increment,
  `pattern` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `commands` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url` text NOT NULL,
  `description` text NOT NULL,
  `uses` int(11) NOT NULL default '0',
  `spam` tinyint(4) NOT NULL default '0',
  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_use_date` datetime default '0000-00-00 00:00:00',
  `golden_egg_date` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

