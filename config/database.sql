-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

--
-- Table `tl_voting`
--

CREATE TABLE `tl_voting` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `dateAdded` int(10) unsigned NOT NULL default '0',
  `alias` varbinary(128) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `groups` blob NULL,
  `published` char(1) NOT NULL default '',
  `start` varchar(10) NOT NULL default '',
  `stop` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `alias` (`alias`),
  KEY `published` (`published`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_voting_enquiry`
--

CREATE TABLE `tl_voting_enquiry` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `alias` varbinary(128) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `teaser` text NULL,
  `description` mediumtext NULL,
  `attachments` blob NULL,
  `ayes` smallint(5) unsigned NOT NULL default '0',
  `nays` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_voting_registry`
--

CREATE TABLE `tl_voting_registry` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `voting` int(10) unsigned NOT NULL default '0',
  `member` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `voting` (`voting`),
  KEY `member` (`member`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
