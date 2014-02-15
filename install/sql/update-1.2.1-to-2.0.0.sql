ALTER TABLE `{$db_prefix}_hooks` DROP `type`;
ALTER TABLE `{$db_prefix}_mod_comments` DROP `target_hash`, DROP `object_name`, DROP `object_id`;
UPDATE  `{$db_prefix}_components` SET  `dir` =  'user' WHERE  `dir` = 'usercontrol';
UPDATE  `{$db_prefix}_modules` SET  `dir` =  'usernotify' WHERE  `dir` = 'user_notify';
INSERT INTO `{$db_prefix}_hooks` (`configs`, `dir`, `enabled`) VALUES ('', 'file', '1');
INSERT INTO `{$db_prefix}_hooks` (`configs`, `dir`, `enabled`) VALUES ('', 'mail', '1');
ALTER TABLE `{$db_prefix}_user` CHANGE `aprove` `aprove` varchar(128) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '0' AFTER `token_start`;
ALTER TABLE `{$db_prefix}_user_recovery` CHANGE `hash` `hash` varchar(128) COLLATE 'utf8_general_ci' NOT NULL AFTER `password`;
ALTER TABLE `{$db_prefix}_user_access_level` ADD `permissions` text NOT NULL DEFAULT '';
UPDATE `{$db_prefix}_user_access_level` SET
  `group_id` = '3',
  `group_name` = 'Admin',
  `access_to_admin` = '1',
  `content_view` = '1',
  `content_post` = '1',
  `mod_comment_add` = '1',
  `mod_comment_edit` = '1',
  `mod_comment_delete` = '1',
  `permissions` = 'global/read;global/write;comment/add;comment/edit;comment/delete;global/owner;'
WHERE `group_id` = '3' COLLATE utf8_bin LIMIT 1;
UPDATE `{$db_prefix}_user_access_level` SET
  `group_id` = '2',
  `group_name` = 'Moderator',
  `access_to_admin` = '0',
  `content_view` = '1',
  `content_post` = '1',
  `mod_comment_add` = '1',
  `mod_comment_edit` = '1',
  `mod_comment_delete` = '1',
  `permissions` = 'global/read;global/write;comment/add;comment/edit;comment/delete;'
WHERE `group_id` = '2' COLLATE utf8_bin LIMIT 1;
UPDATE `{$db_prefix}_user_access_level` SET
  `group_id` = '1',
  `group_name` = 'User',
  `access_to_admin` = '0',
  `content_view` = '1',
  `content_post` = '1',
  `mod_comment_add` = '1',
  `mod_comment_edit` = '0',
  `mod_comment_delete` = '0',
  `permissions` = 'global/read;global/write;comment/add;'
WHERE `group_id` = '1' COLLATE utf8_bin LIMIT 1;
UPDATE `{$db_prefix}_user_access_level` SET
  `group_id` = '4',
  `group_name` = 'Only Read',
  `access_to_admin` = '0',
  `content_view` = '1',
  `content_post` = '0',
  `mod_comment_add` = '0',
  `mod_comment_edit` = '0',
  `mod_comment_delete` = '0',
  `permissions` = 'global/read;'
WHERE `group_id` = '4' COLLATE utf8_bin LIMIT 1;
DELETE FROM `{$db_prefix}_user_access_level` WHERE (`group_id` = '5' COLLATE utf8_bin);
ALTER TABLE `{$db_prefix}_user_access_level` DROP `access_to_admin`, DROP `content_view`, DROP `content_post`, DROP `mod_comment_add`, DROP `mod_comment_edit`, DROP `mod_comment_delete`;
DELETE FROM `{$db_prefix}_modules` WHERE `dir` = 'static_includes';