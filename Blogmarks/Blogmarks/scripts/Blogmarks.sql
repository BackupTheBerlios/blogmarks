-- MySQL dump 9.10
--
-- Host: localhost    Database: Blogmarks
-- ------------------------------------------------------
-- Server version	4.0.18

--
-- Table structure for table `bm_Links`
--

CREATE TABLE bm_links (
  id int(11) NOT NULL auto_increment,
  lang varchar(255) default NULL,
  type varchar(255) default NULL,
  href varchar(255) NOT NULL,
  title text,
  charset varchar(255) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY href (href)
) TYPE=MyISAM COMMENT="Liste des liens blogmarks (related, via)";

--
-- Table structure for table `bm_Marks`
--

CREATE TABLE bm_marks (
  id int(11) NOT NULL auto_increment,
  title varchar(255) default NULL,
  issued datetime default '0000-00-00 00:00:00',
  created datetime default '0000-00-00 00:00:00',
  modified datetime NOT NULL default '0000-00-00 00:00:00',
  related int(11) NOT NULL default '0',
  via int(11) default NULL,
  screenshot varchar(255) default NULL,
  author varchar(255) NOT NULL default '0',
  summary text,
  lang varchar(10) default NULL,
  PRIMARY KEY  (id),
  KEY index_author (author),
  KEY index_related (related)
) TYPE=MyISAM COMMENT="Liste des blogmarks";

--
-- Table structure for table `bm_Marks_has_bm_Tags`
--

CREATE TABLE bm_marks_has_bm_tags (
  bm_Marks_id int(11) NOT NULL default '0',
  bm_Tags_id int(11) NOT NULL default '0',
  PRIMARY KEY  (bm_Marks_id,bm_Tags_id),
  KEY index_mark (bm_Marks_id),
  KEY index_tag (bm_Tags_id)
) TYPE=MyISAM COMMENT="Table de liaison entre les marks et les tags";

--
-- Table structure for table `bm_Sessions`
--

CREATE TABLE bm_sessions (
  id varchar(255) NOT NULL default '',
  last_update timestamp(14) NOT NULL,
  data mediumtext NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM COMMENT='Sessions utilisateurs';

--
-- Table structure for table `bm_Tags`
--

CREATE TABLE bm_tags (
  id int(11) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  author varchar(255) NOT NULL default '',
  issued datetime default '0000-00-00 00:00:00',
  modified datetime NOT NULL default '0000-00-00 00:00:00',
  summary text,
  lang varchar(10) default '',
  ico varchar(255) default '',
  PRIMARY KEY  (id),
  KEY index_author (author)
) TYPE=MyISAM COMMENT="Liste des tags";

--
-- Table structure for table `bm_Users`
--

CREATE TABLE bm_users (
  login varchar(255) default NULL,
  pwd varchar(255) default NULL,
  email varchar(255) default NULL,
  url varchar(255) default NULL,
  permlevel enum('1','2') NOT NULL default '1',
  PRIMARY KEY  (login)
) TYPE=MyISAM COMMENT="Liste des utilisateur de blogmarks";

