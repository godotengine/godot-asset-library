
ALTER TABLE `as_assets` ADD `issues_url` VARCHAR(1024) NOT NULL AFTER `browse_url`;
ALTER TABLE `as_asset_edits` ADD `issues_url` VARCHAR(1024) NULL DEFAULT NULL AFTER `browse_url`;