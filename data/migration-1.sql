

ALTER TABLE `as_assets` ADD COLUMN `download_provider` TINYINT NOT NULL AFTER `download_url`;
ALTER TABLE `as_assets` ADD `download_commit` VARCHAR(64) NOT NULL AFTER `download_provider`;

UPDATE `as_assets` SET `download_provider`=0,`download_commit`=
  SUBSTRING(`download_url`,
    LOCATE('/',`download_url`,LOCATE('/',`download_url`,LOCATE('/',`download_url`,20)+1)+1)+1,
    -- Matching the last slash, which is V right below the `V`
    -- https://github.com/.../.../archive/....zip
    --                   ^ This slash is the 19-th character
    LENGTH(`download_url`) - LOCATE('/',`download_url`,LOCATE('/',`download_url`,LOCATE('/',`download_url`,20)+1)+1) - 4
    -- Repeating locate formula :/
  ) WHERE `download_url` RLIKE 'https:\/\/github.com\/[^\/]+\/[^\/]+\/archive\/[^\/]+.zip';

ALTER TABLE `as_assets` DROP COLUMN `download_url`;

ALTER TABLE `as_asset_edits` ADD COLUMN `download_provider` TINYINT NULL AFTER `download_url`;
ALTER TABLE `as_asset_edits` ADD `download_commit` VARCHAR(64) NULL AFTER `download_provider`;

UPDATE `as_asset_edits` SET `download_provider`=0,`download_commit`=
  SUBSTRING(`download_url`,
    LOCATE('/',`download_url`,LOCATE('/',`download_url`,LOCATE('/',`download_url`,20)+1)+1)+1,
    -- Matching the last slash, which is V right below the `V`
    -- https://github.com/.../.../archive/....zip
    --                   ^ This slash is the 19-th character
    LENGTH(`download_url`) - LOCATE('/',`download_url`,LOCATE('/',`download_url`,LOCATE('/',`download_url`,20)+1)+1) - 4
    -- Repeating locate formula :/
  ) WHERE `download_url` RLIKE 'https:\/\/github.com\/[^\/]+\/[^\/]+\/archive\/[^\/]+.zip';

ALTER TABLE `as_asset_edits` DROP COLUMN `download_url`;
