-- Adminer 4.2.4 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `as_assets`;
CREATE TABLE `as_assets` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '6',
  `version` int(11) NOT NULL,
  `version_string` varchar(20) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '1',
  `cost` varchar(25) NOT NULL DEFAULT 'GPLv3',
  `description` text NOT NULL,
  `download_url` varchar(1024) NOT NULL,
  `browse_url` varchar(1024) NOT NULL,
  `icon_url` varchar(1024) NOT NULL,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

INSERT INTO `as_assets` (`asset_id`, `title`, `author_id`, `category_id`, `version`, `version_string`, `rating`, `cost`, `description`, `download_url`, `browse_url`, `icon_url`) VALUES
(1,	'Make Halo',	1,	7,	1,	'1.1',	3,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'https://github.com/reduz/godot-test-addon/archive/master.zip',	'https://github.com/reduz/godot-test-addon',	'http://localhost/public/addonlib/KOBUGE-triangle-small.png'),
(2,	'Game Holifier',	2,	7,	3,	'1.2',	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'',	'',	''),
(3,	'Unity2Godot',	3,	7,	0,	'0.0',	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'',	'',	''),
(4,	'Pixel Painter',	3,	7,	89,	'1.40.2',	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'',	'',	'');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

INSERT INTO `as_asset_previews` (`preview_id`, `asset_id`, `type`, `link`, `thumbnail`) VALUES
(1,	1,	'video',	'https://www.youtube.com/watch?v=M0YkQ4YKOSw',	''),
(2,	1,	'image',	'http://localhost/public/addonlib/KOBUGE_Splash.png',	'http://localhost/public/addonlib/KOBUGE-triangle-small.png');

DROP TABLE IF EXISTS `as_authors`;
CREATE TABLE `as_authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author` varchar(50) NOT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `as_authors` (`author_id`, `author`) VALUES
(1,	'Bill Gates'),
(2,	'Pope Francis'),
(3,	'Roland Peterson'),
(4,	'DaVinci Digital');

DROP TABLE IF EXISTS `as_categories`;
CREATE TABLE `as_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(25) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

INSERT INTO `as_categories` (`category_id`, `category`) VALUES
(1,	'2D Tools'),
(2,	'3D Tools'),
(4,	'Materials'),
(7,	'Misc'),
(6,	'Scripts'),
(3,	'Shaders'),
(5,	'Tools');

-- 2016-04-30 05:25:04
