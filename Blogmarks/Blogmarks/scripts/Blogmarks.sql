-- MySQL dump 9.10
--
-- Host: localhost    Database: Blogmarks
-- ------------------------------------------------------
-- Server version	4.0.18

--
-- Table structure for table `bm_Links`
--

CREATE TABLE bm_Links (
  id int(11) NOT NULL auto_increment,
  lang varchar(255) default NULL,
  type varchar(255) default NULL,
  href varchar(255) default NULL,
  title text,
  charset varchar(255) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY href (href)
) TYPE=MyISAM;

--
-- Table structure for table `bm_Marks`
--

CREATE TABLE bm_Marks (
  id int(11) NOT NULL auto_increment,
  href int(11) NOT NULL default '0',
  bm_Users_id int(11) NOT NULL default '0',
  title varchar(255) default NULL,
  summary text,
  screenshot varchar(255) default NULL,
  issued datetime default '0000-00-00 00:00:00',
  created datetime default NULL,
  modified datetime NOT NULL default '0000-00-00 00:00:00',
  lang varchar(255) default NULL,
  via int(11) default NULL,
  source int(11) default NULL,
  PRIMARY KEY  (id),
  KEY bm_Marks_FKIndex1 (bm_Users_id),
  KEY bm_Marks_FKIndex2 (href)
) TYPE=MyISAM;

--
-- Table structure for table `bm_Marks_has_bm_Tags`
--

CREATE TABLE bm_Marks_has_bm_Tags (
  bm_Marks_id int(11) NOT NULL default '0',
  bm_Tags_id varchar(255) NOT NULL default '',
  PRIMARY KEY  (bm_Marks_id,bm_Tags_id),
  KEY bm_Marks_has_bm_Tags_FKIndex1 (bm_Marks_id)
) TYPE=MyISAM;

--
-- Table structure for table `bm_Sessions`
--

CREATE TABLE bm_Sessions (
  id varchar(255) NOT NULL default '',
  last_update timestamp(14) NOT NULL,
  data mediumtext NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM COMMENT='Sessionns utilisateurs';

--
-- Table structure for table `bm_Tags`
--

CREATE TABLE bm_Tags (
  id varchar(255) NOT NULL default '',
  subTagOf int(11) default NULL,
  bm_Users_id int(11) NOT NULL default '0',
  summary text,
  lang int(10) unsigned default NULL,
  status set('public','private') NOT NULL default 'public',
  PRIMARY KEY  (id),
  KEY bm_Tags_FKIndex1 (bm_Users_id)
) TYPE=MyISAM;

--
-- Table structure for table `bm_Users`
--

CREATE TABLE bm_Users (
  id int(11) NOT NULL auto_increment,
  login varchar(255) default NULL,
  pwd varchar(255) default NULL,
  email varchar(255) default NULL,
  permlevel enum('0','1','2') NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY login (login)
) TYPE=MyISAM;

