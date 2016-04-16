-- Adminer 4.2.4 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `as_assets`;
CREATE TABLE `as_assets` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(25) NOT NULL,
  `author_id` int(11) NOT NULL DEFAULT '0',
  `category` varchar(25) NOT NULL DEFAULT 'Misc',
  `category_id` int(11) NOT NULL DEFAULT '6',
  `rating` int(11) NOT NULL DEFAULT '1',
  `cost` varchar(25) NOT NULL DEFAULT 'GPLv3',
  `description` text NOT NULL,
  `download_url` varchar(1024) NOT NULL,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_assets` (`asset_id`, `title`, `author`, `author_id`, `category`, `category_id`, `rating`, `cost`, `description`, `download_url`) VALUES
(1,	'Make Halo',	'Bill Gates',	0,	'Misc',	7,	2,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'https://github.com/reduz/godot-test-addon/archive/master.zip'),
(2,	'Game Holifier',	'Pope Francis',	0,	'Misc',	7,	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	''),
(3,	'Unity2Godot',	'Roland Peterson',	0,	'Misc',	7,	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	''),
(4,	'Pixel Painter',	'DaVinci Digital',	0,	'Misc',	7,	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'');

DROP TABLE IF EXISTS `as_asset_previews`;
CREATE TABLE `as_asset_previews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `type` enum('image','video') NOT NULL,
  `link` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_asset_previews` (`id`, `asset_id`, `type`, `link`) VALUES
(1,	1,	'video',	'https://www.youtube.com/watch?v=M0YkQ4YKOSw'),
(2,	1,	'image',	'https://lut.im/z45mZT86E5/pchMIK8JppcgLTXV.png');

DROP TABLE IF EXISTS `as_categories`;
CREATE TABLE `as_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_categories` (`id`, `name`) VALUES
(1,	'2D Tools'),
(2,	'3D Tools'),
(4,	'Materials'),
(7,	'Misc'),
(6,	'Scripts'),
(3,	'Shaders'),
(5,	'Tools');

-- 2016-04-16 14:31:55
