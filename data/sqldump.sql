-- Adminer 4.2.4 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `as_assets`;
CREATE TABLE `as_assets` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '6',
  `version` int(11) NOT NULL,
  `version_string` varchar(20) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '1',
  `cost` varchar(25) NOT NULL DEFAULT 'GPLv3',
  `description` text NOT NULL,
  `download_url` varchar(1024) NOT NULL,
  `browse_url` varchar(1024) NOT NULL,
  `icon_url` varchar(1024) NOT NULL,
  `accepted` enum('UNACCEPTED','UPDATED','ACCEPTED') NOT NULL DEFAULT 'UNACCEPTED',
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `as_assets` (`asset_id`, `title`, `user_id`, `category_id`, `version`, `version_string`, `rating`, `cost`, `description`, `download_url`, `browse_url`, `icon_url`, `accepted`) VALUES
(1,	'Make Halo',	1,	7,	1,	'1.1',	3,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'https://github.com/reduz/godot-test-addon/archive/master.zip',	'https://github.com/reduz/godot-test-addon',	'http://localhost/public/addonlib/KOBUGE-triangle-small.png',	'ACCEPTED'),
(2,	'Game Holifier',	2,	7,	3,	'1.2',	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'',	'',	'',	'UNACCEPTED'),
(3,	'Unity2Godot',	3,	7,	0,	'0.0',	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'',	'',	'',	'UPDATED'),
(4,	'Pixel Painter',	3,	7,	89,	'1.40.2',	1,	'GPLv3',	'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies diam sed risus ultricies malesuada. Vivamus non vulputate massa.',	'',	'',	'',	'ACCEPTED'),
(8,	'Minilens!',	5,	6,	0,	'v0.0.1',	0,	'GPLv3',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	'UNACCEPTED'),
(9,	'Minilens!',	5,	6,	4,	'v1.2',	0,	'GPLv3',	'1000 years after post-apocalyptic Earth, many form of lifes went extinct, including humans. An alien robot series, called Minilens, are cleaning up earth and collecting surviving flora. The game is won by destroying all Radioactive Barrels and collecting surviving flora. The drawback is that Minilens can\'t jump.',	'https://github.com/KOBUGE-Games/minilens/archive/v1.2.zip',	'https://github.com/KOBUGE-Games/minilens',	'https://raw.githubusercontent.com/KOBUGE-Games/minilens/master/icon.png',	'UNACCEPTED');

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
  `session_token` varbinary(24) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `session_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `as_users` (`user_id`, `username`, `email`, `password_hash`, `session_token`) VALUES
(1,	'aaaaz',	'test@user.name',	'$2y$12$7SqfYX2UD17vPQkbaOK.C.t7KbrZ3v09oksFMqBtQB9TOTqyYAAuC',	NULL),
(2,	'test_user',	'test@user.name',	'$2y$12$0SkpFOqTEKUSY/78oWNPouYmzIM4UbMeWpw6.jeKgAWLKhHlioyyy',	NULL),
(3,	'test_test-f5a26d68038bf',	'test@user.name',	'$2y$12$itPZ3LneC3p7SfoIBGUdxeVeFmg0ATHu0kkKMt8fytsJKBBMm3Nyy',	NULL),
(4,	'test-9e620348d0ed3',	'test@user.name',	'$2y$12$Nvlv9RRl0xuYNrX7usLTl.1OcYY95Cjod.Xn1yKw81jCxUYhMEMjG',	NULL),
(5,	'test-598e3f2f3e9a7',	'test@user.name',	'$2y$12$27pa0RbFvsy0k0tGh98ClOhLAW.BtaPL4ABGysU0OvwqDncFcyFo.',	NULL);

-- 2016-06-02 19:13:01
