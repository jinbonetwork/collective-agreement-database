DROP TABLE IF EXISTS `cadb_agreement`;
CREATE TABLE `cadb_agreement` (
	nid     int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	did     int(10) NOT NULL DEFAULT 1,
	oid     int(10) NOT NULL DEFAULT 0,
	vid     int(10) NOT NULL DEFAULT 1,
	content mediumtext,
	created int(10) NOT NULL DEFAULT 0,
	current char(1) NOT NULL DEFAULT '1',

	KEY `DID` (`nid`,`did`),
	KEY `OID` (`oid`,`vid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_fields`;
CREATE TABLE `cadb_fields` (
	`fid`		int(10) NOT NULL AUTO_INCREMENT,
	`table`		char(128) NOT NULL DEFAULT '',
	`idx`		smallint(5) NOT NULL DEFAULT 1,
	`subject`	char(255),
	`iscolumn`	char(1) DEFAULT 0,
	`type`		char(20),
	`multiple`	char(1) DEFAULT 0,
	`required`	char(1) DEFAULT 0,
	`cid`		int(10),
	`active`	char(1) DEFAULT '1',
	`system`	char(1) DEFAULT '0',

	PRIMARY KEY (`table`,`idx`,`fid`),
	KEY `FID` (`table`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_guide`;
CREATE TABLE `cadb_guide` (
	`nid`		int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`oid`		int(10) NOT NULL DEFAULT 1,
	`vid`		int(10) NOT NULL DEFAULT 1,
	`year`		smallint(10) NOT NULL DEFAULT 1,
	`subject`	char(255) NOT NULL DEFAULT '',
	`custom`	text,
	`created`	int(10) NOT NULL DEFAULT 0,
	`current`	char(1) NOT NULL DEFAULT '1',

	KEY `OID` (`oid`,`vid`,`year`),
	KEY `YEAR` (`year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_guide_clause`;
CREATE TABLE `cadb_guide_clause` (
	`id`		int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`nid`		int(10) NOT NULL DEFAULT 0,
	`idx`		smallint(10) DEFAULT 0,
	`content`	text,
	`custom`	mediumtext,

	KEY `NID`	(`nid`,`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_log`;
CREATE TABLE `cadb_log` (
	`id`		bigint(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`action`	char(20) not null default '',
	`oid`		int(10) default 0,
	`fid`		int(10) default 0,
	`vid`		int(10) default 0,
	`editor`	int(10) not null default 0,
	`name`		char(255),
	`modified`	int(10) not null default 0,
	`ipaddress`	char(20),
	`memo`		char(255),

	KEY `Action` (`action`,`oid`,`fid`,`vid`),
	KEY `Editor` (`editor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS cadb_organize;
CREATE TABLE cadb_organize (
	`oid`		int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`vid`		int(10) NOT NULL DEFAULT 1,
	`p1`		int(10),
	`p2`		int(10),
	`p3`		int(10),
	`depth`		smallint(3),
	`p1name`	char(128),
	`p2name`	char(128),
	`p3name`	char(128),
	`name`		char(128) NOT NULL DEFAULT '',
	`fullname`	char(128),
	`custom`	text,
	`current`	char(1) DEFAULT 1,
	`active`	char(1) DEFAULT 1,
	`from`		int(10) DEFAULT 0,
	`to`		int(10) DEFAULT 0,
	`created`	int(10) NOT NULL DEFAULT 0,

	KEY `ID` (`oid`,`current`),
	KEY `DEPTH` (`depth`),
	KEY `P` (`p1`,`p2`,`p3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS cadb_privilege;
CREATE TABLE cadb_privilege (
	`uid`		int(10) NOT NULL PRIMARY KEY,
	`user_id`	char(255) NOT NULL DEFAULT '',
	`oid`		int(10) NOT NULL DEFAULT 0,
	`role`		smallint(5) NOT NULL DEFAULT 5,

	KEY `USER_ID` (`user_id`),
	KEY `OID` (`oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_taxonomy`;
CREATE TABLE `cadb_taxonomy` (
	`cid`		int(5) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`subject`	char(255) NOT NULL DEFAULT '',
	`skey`		char(1) DEFAULT 0,
	`active`	char(1) DEFAULT 1,

	KEY `SKEY` (`skey`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_taxonomy_terms`;
CREATE TABLE `cadb_taxonomy_terms` (
	`vid`		int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`tid`		int(10) NOT NULL DEFAULT 0,
	`cid`		int(10) NOT NULL DEFAULT 0,
	`parent`	int(10) DEFAULT 0,
	`idx`		smallint(5) NOT NULL DEFAULT 1,
	`nsubs`		smallint(5) DEFAULT 0,
	`name`		char(128) NOT NULL DEFAULT '',
	`current`	char(1) DEFAULT 1,
	`active`	char(1) DEFAULT 1,
	`from`		int(10) DEFAULT 0,
	`to`		int(10) DEFAULT 0,
	`created`	int(10) NOT NULL DEFAULT 0,

	KEY `TID` (`tid`,`vid`,`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cadb_taxonomy_term_relative`;
CREATE TABLE `cadb_taxonomy_term_relative` (
	`tid`		int(10) NOT NULL DEFAULT 0,
	`table`		char(128) NOT NULL DEFAULT '',
	`rid`		int(10) NOT NULL DEFAULT 0,
	`fid`		smallint(5) NOT NULL DEFAULT 0,

	PRIMARY KEY `TID` (`tid`,`table`,`rid`),
	KEY `FID` (`table`,`rid`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
