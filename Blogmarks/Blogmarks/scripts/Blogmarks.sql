# phpMyAdmin SQL Dump
# version 2.5.3-rc2
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: May 19, 2004 at 01:37 PM
# Server version: 4.0.18
# PHP Version: 4.3.4
# 
# Database : `Blogmarks`
# 

# --------------------------------------------------------

#
# Table structure for table `bm_Links`
#
# Creation: Apr 29, 2004 at 04:45 PM
# Last update: May 07, 2004 at 03:37 PM
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
) TYPE=MyISAM AUTO_INCREMENT=22 ;

# --------------------------------------------------------

#
# Table structure for table `bm_Marks`
#
# Creation: Apr 30, 2004 at 12:06 PM
# Last update: May 07, 2004 at 05:39 PM
#

DROP TABLE IF EXISTS `bm_Marks`;
CREATE TABLE `bm_Marks` (
  `id` int(11) NOT NULL auto_increment,
  `href` int(11) NOT NULL default '0',
  `bm_Users_id` int(11) NOT NULL default '0',
  `title` varchar(255) default NULL,
  `summary` text,
  `screenshot` varchar(255) default NULL,
  `issued` datetime default '0000-00-00 00:00:00',
  `created` datetime default NULL,
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `lang` varchar(255) default NULL,
  `via` int(11) default NULL,
  `source` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `bm_Marks_FKIndex1` (`bm_Users_id`),
  KEY `bm_Marks_FKIndex2` (`href`)
) TYPE=MyISAM AUTO_INCREMENT=101 

/* COMMENTS FOR TABLE `bm_Marks`:
    `bm_Users_id`
        `Id de l'utilisateur possédant le Mark`
    `created`
        `Date de création`
    `href`
        `Id du Link désignant l'emplacement de la ressource`
    `issued`
        `Si 0 ou > NOW, le Mark est privé`
    `lang`
        `Langue (code ISO)`
    `modified`
        `Date de la dernière modification`
    `source`
        `URL de la source de l'information`
    `summary`
        `Description`
    `title`
        `Titre du Mark`
    `via`
        `Id du Link désignant le via (sic)`
*/

/* RELATIONS FOR TABLE `bm_Marks`:
    `bm_Users_id`
        `bm_Users` -> `id`
    `href`
        `bm_Links` -> `id`
    `source`
        `bm_Links` -> `id`
    `via`
        `bm_Links` -> `id`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Marks_has_bm_Tags`
#
# Creation: Apr 29, 2004 at 04:45 PM
# Last update: May 07, 2004 at 05:39 PM
#

DROP TABLE IF EXISTS `bm_Marks_has_bm_Tags`;
CREATE TABLE `bm_Marks_has_bm_Tags` (
  `bm_Marks_id` int(11) NOT NULL default '0',
  `bm_Tags_id` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bm_Marks_id`,`bm_Tags_id`),
  KEY `bm_Marks_has_bm_Tags_FKIndex1` (`bm_Marks_id`)
) TYPE=MyISAM

/* RELATIONS FOR TABLE `bm_Marks_has_bm_Tags`:
    `bm_Tags_id`
        `bm_Tags` -> `id`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Sessions`
#
# Creation: May 05, 2004 at 11:00 AM
# Last update: May 05, 2004 at 11:04 AM
#

DROP TABLE IF EXISTS `bm_Sessions`;
CREATE TABLE `bm_Sessions` (
  `id` varchar(255) NOT NULL default '',
  `last_update` timestamp(14) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Sessionns utilisateurs'

/* COMMENTS FOR TABLE `bm_Sessions`:
    `data`
        `Données de session`
    `id`
        `identifiant unique de la session`
    `last_update`
        `date d'expiration de la session`
*/;

# --------------------------------------------------------

#
# Table structure for table `bm_Tags`
#
# Creation: Apr 29, 2004 at 04:45 PM
# Last update: May 07, 2004 at 03:48 PM
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
# Creation: Apr 29, 2004 at 04:45 PM
# Last update: Apr 29, 2004 at 04:45 PM
#

DROP TABLE IF EXISTS `bm_Users`;
CREATE TABLE `bm_Users` (
  `id` int(11) NOT NULL auto_increment,
  `login` varchar(255) default NULL,
  `pwd` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `permlevel` enum('0','1','2') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=6 

/* COMMENTS FOR TABLE `bm_Users`:
    `permlevel`
        `Niveau de permission de l'utilisateur`
*/;
    