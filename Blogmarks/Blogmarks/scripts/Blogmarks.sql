# phpMyAdmin SQL Dump
# version 2.5.3-rc2
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Apr 06, 2004 at 01:32 PM
# Server version: 4.0.14
# PHP Version: 4.3.4
# 
# Database : `Blogmarks`
# 

# --------------------------------------------------------

#
# Table structure for table `bm_Links`
#
# Creation: Mar 05, 2004 at 06:07 PM
# Last update: Mar 31, 2004 at 12:05 PM
#

DROP TABLE IF EXISTS `bm_Links`;
CREATE TABLE `bm_Links` (
  `id` int(11) NOT NULL auto_increment,
  `lang` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `href` varchar(255) default NULL,
  `title` text,
  `charset` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=8 ;

# --------------------------------------------------------

#
# Table structure for table `bm_Marks`
#
# Creation: Mar 15, 2004 at 01:10 PM
# Last update: Mar 31, 2004 at 12:05 PM
#

DROP TABLE IF EXISTS `bm_Marks`;
CREATE TABLE `bm_Marks` (
  `id` int(11) NOT NULL auto_increment,
  `bm_Links_id` int(11) NOT NULL default '0',
  `bm_Users_id` int(11) NOT NULL default '0',
  `title` varchar(255) default NULL,
  `summary` text,
  `screenshot` varchar(255) default NULL,
  `issued` datetime default NULL,
  `created` datetime default NULL,
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `lang` varchar(255) default NULL,
  `via` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `bm_Marks_FKIndex1` (`bm_Users_id`),
  KEY `bm_Marks_FKIndex2` (`bm_Links_id`)
) TYPE=MyISAM AUTO_INCREMENT=64 

/* RELATIONS FOR TABLE `bm_Marks`:
    `bm_Links_id`
        `bm_Links` -> `id`
    `bm_Users_id`
        `bm_Users` -> `id`
    `via`
        `bm_Links` -> `id`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Marks_has_bm_Tags`
#
# Creation: Mar 17, 2004 at 11:39 AM
# Last update: Mar 31, 2004 at 12:08 PM
#

DROP TABLE IF EXISTS `bm_Marks_has_bm_Tags`;
CREATE TABLE `bm_Marks_has_bm_Tags` (
  `bm_Marks_id` int(11) NOT NULL default '0',
  `bm_Tags_id` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bm_Marks_id`,`bm_Tags_id`),
  KEY `bm_Marks_has_bm_Tags_FKIndex1` (`bm_Marks_id`)
) TYPE=MyISAM

/* RELATIONS FOR TABLE `bm_Marks_has_bm_Tags`:
    `bm_Marks_id`
        `bm_Marks` -> `id`
    `bm_Tags_id`
        `bm_Tags` -> `id`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Sessions`
#
# Creation: Mar 30, 2004 at 03:09 PM
# Last update: Mar 30, 2004 at 04:34 PM
#

DROP TABLE IF EXISTS `bm_Sessions`;
CREATE TABLE `bm_Sessions` (
  `id` varchar(255) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `expire` timestamp(14) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Sessionns utilisateurs'

/* COMMENTS FOR TABLE `bm_Sessions`:
    `data`
        `Données de session`
    `expire`
        `date d'expiration de la session`
    `id`
        `identifiant unique de la session`
    `user_id`
        `identifiant de l'utilisateur lié à la session`
*/

/* RELATIONS FOR TABLE `bm_Sessions`:
    `user_id`
        `bm_Users` -> `id`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Tags`
#
# Creation: Mar 17, 2004 at 11:39 AM
# Last update: Mar 31, 2004 at 12:05 PM
#

DROP TABLE IF EXISTS `bm_Tags`;
CREATE TABLE `bm_Tags` (
  `id` varchar(255) NOT NULL default '',
  `subTagOf` int(11) default NULL,
  `bm_Users_id` int(11) NOT NULL default '0',
  `summary` text,
  `lang` int(10) unsigned default NULL,
  `status` set('public','private') default NULL,
  PRIMARY KEY  (`id`),
  KEY `bm_Tags_FKIndex1` (`bm_Users_id`)
) TYPE=MyISAM

/* RELATIONS FOR TABLE `bm_Tags`:
    `bm_Users_id`
        `bm_Users` -> `id`
    `subTagOf`
        `bm_Tags` -> `id`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Users`
#
# Creation: Mar 10, 2004 at 04:28 PM
# Last update: Mar 30, 2004 at 01:32 PM
#

DROP TABLE IF EXISTS `bm_Users`;
CREATE TABLE `bm_Users` (
  `id` int(11) NOT NULL auto_increment,
  `login` varchar(255) default NULL,
  `pwd` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `permlevel` enum('0','1','2') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=4 

/* COMMENTS FOR TABLE `bm_Users`:
    `permlevel`
        `Niveau de permission de l'utilisateur`
*/;
