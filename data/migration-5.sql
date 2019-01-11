
ALTER TABLE `as_assets` ADD `godot_version` INT(7) NOT NULL AFTER `category_id`, ADD INDEX `godot_version_index` (`godot_version`);
UPDATE `as_assets` SET `godot_version` = 20100 WHERE `godot_version` = 0;

ALTER TABLE `as_asset_edits`  ADD `godot_version` INT(7) NULL  AFTER `category_id`,  ADD   INDEX  `godot_version_index` (`godot_version`);
UPDATE `as_asset_edits` SET `godot_version` = 20100 WHERE `asset_id` = -1;
