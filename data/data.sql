SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `as_assets`;
CREATE TABLE `as_assets` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '6',
  `godot_version` int(7) NOT NULL,
  `version` int(11) NOT NULL,
  `version_string` varchar(20) NOT NULL,
  `cost` varchar(25) NOT NULL DEFAULT 'GPLv3',
  `rating` int(11) NOT NULL DEFAULT '1',
  `support_level` tinyint(4) NOT NULL,
  `download_provider` tinyint(4) NOT NULL,
  `download_commit` varchar(2048) NOT NULL,
  `browse_url` varchar(1024) NOT NULL,
  `issues_url` varchar(1024) NOT NULL,
  `icon_url` varchar(1024) NOT NULL,
  `searchable` tinyint(1) NOT NULL DEFAULT '0',
  `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `as_asset_edits`;
CREATE TABLE `as_asset_edits` (
  `edit_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `category_id` int(11) DEFAULT NULL,
  `godot_version` int(7) NOT NULL,
  `version_string` varchar(11) DEFAULT NULL,
  `cost` varchar(25) DEFAULT NULL,
  `download_provider` tinyint(4) DEFAULT NULL,
  `download_commit` varchar(2048) DEFAULT NULL,
  `browse_url` varchar(1024) DEFAULT NULL,
  `issues_url` varchar(1024) DEFAULT NULL,
  `icon_url` varchar(1024) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `reason` text NOT NULL,
  `submit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`edit_id`),
  KEY `asset_id` (`asset_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `as_asset_edit_previews`;
CREATE TABLE `as_asset_edit_previews` (
  `edit_preview_id` int(11) NOT NULL AUTO_INCREMENT,
  `edit_id` int(11) NOT NULL,
  `preview_id` int(11) NOT NULL,
  `type` enum('image','video') DEFAULT NULL,
  `link` varchar(1024) DEFAULT NULL,
  `thumbnail` varchar(1024) DEFAULT NULL,
  `operation` tinyint(4) NOT NULL,
  PRIMARY KEY (`edit_preview_id`),
  KEY `asset_id` (`edit_id`),
  KEY `type` (`type`),
  KEY `preview_id` (`preview_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `as_asset_previews`;
CREATE TABLE `as_asset_previews` (
  `preview_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `type` enum('image','video') NOT NULL,
  `link` varchar(1024) NOT NULL,
  `thumbnail` varchar(1024) NOT NULL,
  PRIMARY KEY (`preview_id`),
  KEY `asset_id` (`asset_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `as_categories`;
CREATE TABLE `as_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(25) NOT NULL,
  `category_type` tinyint(4) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_categories` (`category_id`, `category`, `category_type`) VALUES
(1,	'2D Tools',	0),
(2,	'3D Tools',	0),
(3,	'Shaders',	0),
(4,	'Materials',	0),
(5,	'Tools',	0),
(6,	'Scripts',	0),
(7,	'Misc',	0),
(8,	'Templates',	1),
(9,	'Projects',	1),
(10,	'Demos',	1);

DROP TABLE IF EXISTS `as_users`;
CREATE TABLE `as_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(1024) NOT NULL,
  `password_hash` varchar(64) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `session_token` varbinary(24) DEFAULT NULL,
  `reset_token` binary(24) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `reset_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- add indexes
ALTER TABLE `as_assets` ADD INDEX `godot_version_index` (`godot_version`);
ALTER TABLE `as_asset_edits` ADD INDEX `godot_version_index` (`godot_version`);
ALTER TABLE `as_users` ADD INDEX (`reset_token`);
