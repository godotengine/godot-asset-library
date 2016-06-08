-- Adminer 4.2.4 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `addonlib`;
CREATE DATABASE `addonlib` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `addonlib`;

DROP TABLE IF EXISTS `as_assets`;
CREATE TABLE `as_assets` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '6',
  `version` int(11) NOT NULL,
  `version_string` varchar(20) NOT NULL,
  `cost` varchar(25) NOT NULL DEFAULT 'GPLv3',
  `rating` int(11) NOT NULL DEFAULT '1',
  `download_url` varchar(1024) NOT NULL,
  `browse_url` varchar(1024) NOT NULL,
  `icon_url` varchar(1024) NOT NULL,
  `searchable` tinyint(1) NOT NULL DEFAULT '0',
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_assets` (`asset_id`, `user_id`, `title`, `description`, `category_id`, `version`, `version_string`, `cost`, `rating`, `download_url`, `browse_url`, `icon_url`, `searchable`, `modify_date`) VALUES
(1,	1,	'Make Halo',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	7,	1,	'1.1',	'GPLv3',	3,	'https://github.com/reduz/godot-test-addon/archive/master.zip',	'https://github.com/reduz/godot-test-addon',	'http://localhost/public/addonlib/KOBUGE-triangle-small.png',	0,	'2016-06-07 22:39:19'),
(2,	2,	'Game Holifier',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	7,	3,	'1.2',	'GPLv3',	1,	'',	'',	'',	1,	'2016-06-06 18:55:03'),
(3,	3,	'Unity2Godot',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	7,	0,	'0.0',	'GPLv3',	1,	'',	'',	'',	0,	'2016-06-07 22:39:16'),
(4,	3,	'Pixel Painter',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	7,	89,	'1.40.2',	'GPLv3',	1,	'',	'',	'',	0,	'2016-06-07 22:39:14'),
(8,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	6,	7,	'v1.2',	'GPLv3',	0,	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	1,	'2016-06-06 18:55:03'),
(9,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	6,	4,	'v1.2',	'GPLv3',	0,	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	1,	'2016-06-06 18:55:03'),
(10,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	3,	'v0.0.1',	'GPLv3',	0,	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	0,	'2016-06-07 22:47:38'),
(11,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	1,	'v0.0.1',	'GPLv3',	0,	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	0,	'2016-06-08 18:40:10');

DROP TABLE IF EXISTS `as_asset_edits`;
CREATE TABLE `as_asset_edits` (
  `edit_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `category_id` int(11) DEFAULT NULL,
  `version_string` varchar(11) DEFAULT NULL,
  `cost` varchar(25) DEFAULT NULL,
  `download_url` varchar(1024) DEFAULT NULL,
  `browse_url` varchar(1024) DEFAULT NULL,
  `icon_url` varchar(1024) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `reason` text NOT NULL,
  `submit_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`edit_id`),
  KEY `asset_id` (`asset_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `as_asset_edits` (`edit_id`, `asset_id`, `user_id`, `title`, `description`, `category_id`, `version_string`, `cost`, `download_url`, `browse_url`, `icon_url`, `status`, `reason`, `submit_date`, `modify_date`) VALUES
(1,	-1,	1,	'Title',	'Desc.',	7,	'v0.1',	'GPLv4',	'DOWNLOAD__',	NULL,	NULL,	0,	'',	'2016-06-03 17:44:44',	'2016-06-03 18:00:39'),
(2,	-1,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	0,	'',	'2016-06-03 22:25:47',	'2016-06-03 22:25:47'),
(3,	8,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	5,	NULL,	'GPLv3',	NULL,	NULL,	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	0,	'',	'2016-06-03 22:27:05',	'2016-06-03 23:03:19'),
(4,	-1,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	0,	'',	'2016-06-03 22:56:41',	'2016-06-03 22:56:41'),
(5,	8,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	6,	NULL,	'GPLv3',	NULL,	NULL,	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	0,	'',	'2016-06-03 23:17:22',	'2016-06-03 23:17:22'),
(6,	8,	5,	NULL,	NULL,	5,	'v1.2',	NULL,	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	NULL,	2,	'',	'2016-06-03 23:17:30',	'2016-06-03 23:56:41'),
(7,	8,	5,	NULL,	NULL,	NULL,	'v1.2',	NULL,	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	NULL,	3,	'Stupid edit',	'2016-06-03 23:57:04',	'2016-06-06 17:12:49'),
(8,	-1,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	1,	'',	'2016-06-06 17:30:16',	'2016-06-06 17:30:25'),
(9,	-1,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	2,	'',	'2016-06-07 22:32:24',	'2016-06-07 22:40:58'),
(10,	-1,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	2,	'',	'2016-06-07 22:43:01',	'2016-06-07 22:43:08'),
(11,	10,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	3,	'Stupid edit',	'2016-06-07 22:44:46',	'2016-06-07 22:51:11'),
(12,	-1,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	5,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	3,	'Stupid edit',	'2016-06-08 18:30:38',	'2016-06-08 18:39:43'),
(13,	11,	5,	'Minilens!',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	7,	'v0.0.1',	'GPLv3',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	2,	'',	'2016-06-08 18:39:54',	'2016-06-08 18:40:10');

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

INSERT INTO `as_asset_previews` (`preview_id`, `asset_id`, `type`, `link`, `thumbnail`) VALUES
(1,	1,	'video',	'https://www.youtube.com/watch?v=M0YkQ4YKOSw',	''),
(2,	1,	'image',	'http://localhost/public/addonlib/KOBUGE_Splash.png',	'http://localhost/public/addonlib/KOBUGE-triangle-small.png');

DROP TABLE IF EXISTS `as_categories`;
CREATE TABLE `as_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(25) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_categories` (`category_id`, `category`) VALUES
(1,	'2D Tools'),
(2,	'3D Tools'),
(4,	'Materials'),
(7,	'Misc'),
(6,	'Scripts'),
(3,	'Shaders'),
(5,	'Tools');

DROP TABLE IF EXISTS `as_users`;
CREATE TABLE `as_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(1024) NOT NULL,
  `password_hash` varchar(64) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `session_token` varbinary(24) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `session_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `as_users` (`user_id`, `username`, `email`, `password_hash`, `type`, `session_token`) VALUES
(1,	'aaaaz',	'test@user.name',	'$2y$12$7SqfYX2UD17vPQkbaOK.C.t7KbrZ3v09oksFMqBtQB9TOTqyYAAuC',	0,	NULL),
(2,	'test_user',	'test@user.name',	'$2y$12$0SkpFOqTEKUSY/78oWNPouYmzIM4UbMeWpw6.jeKgAWLKhHlioyyy',	0,	NULL),
(3,	'test_test-f5a26d68038bf',	'test@user.name',	'$2y$12$itPZ3LneC3p7SfoIBGUdxeVeFmg0ATHu0kkKMt8fytsJKBBMm3Nyy',	0,	NULL),
(4,	'test-9e620348d0ed3',	'test@user.name',	'$2y$12$Nvlv9RRl0xuYNrX7usLTl.1OcYY95Cjod.Xn1yKw81jCxUYhMEMjG',	0,	NULL),
(5,	'test-598e3f2f3e9a7',	'test@user.name',	'$2y$12$27pa0RbFvsy0k0tGh98ClOhLAW.BtaPL4ABGysU0OvwqDncFcyFo.',	50,	NULL);

-- 2016-06-08 17:20:11
