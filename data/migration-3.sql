
ALTER TABLE `as_users` ADD `reset_token` BINARY(32) NULL DEFAULT NULL AFTER `session_token`, ADD INDEX (`reset_token`);
ALTER TABLE `as_users` CHANGE `session_token` `session_token` BINARY(24) NULL DEFAULT NULL;
