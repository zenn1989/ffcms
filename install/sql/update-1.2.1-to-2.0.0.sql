ALTER TABLE `{$db_prefix}_mod_comments` DROP `target_hash`, DROP `object_name`, DROP `object_id`;
ALTER TABLE `{$db_prefix}_user` CHANGE `aprove` `aprove` varchar(128) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '0' AFTER `token_start`;
ALTER TABLE `{$db_prefix}_user_recovery` CHANGE `hash` `hash` varchar(128) COLLATE 'utf8_general_ci' NOT NULL AFTER `password`;
CREATE TABLE `{$db_prefix}_extensions` (
  `id` int(24) NOT NULL AUTO_INCREMENT,
  `type` enum('components','modules','hooks') NOT NULL,
  `configs` text NOT NULL,
  `dir` varchar(128) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `path_choice` tinyint(1) NOT NULL,
  `path_allow` varchar(1024) NOT NULL,
  `path_deny` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{$db_prefix}_extensions` (`id`, `type`, `configs`, `dir`, `enabled`, `path_choice`, `path_allow`, `path_deny`) VALUES
  (1,	'components',	'',	'static',	1,	0,	'',	''),
  (2,	'components',	'a:12:{s:13:\"login_captcha\";s:1:\"0\";s:16:\"register_captcha\";s:1:\"1\";s:15:\"register_aprove\";s:1:\"0\";s:10:\"use_openid\";s:1:\"1\";s:12:\"profile_view\";s:1:\"1\";s:15:\"wall_post_count\";s:1:\"5\";s:16:\"marks_post_count\";s:1:\"5\";s:17:\"friend_page_count\";s:2:\"10\";s:15:\"wall_post_delay\";s:2:\"30\";s:8:\"pm_count\";s:1:\"5\";s:12:\"balance_view\";s:1:\"0\";s:14:\"userlist_count\";s:2:\"10\";}',	'user',	1,	0,	'',	''),
  (3,	'components',	'a:10:{s:15:\"count_news_page\";s:1:\"5\";s:17:\"short_news_length\";s:3:\"200\";s:18:\"enable_views_count\";s:1:\"1\";s:14:\"enable_useradd\";s:1:\"0\";s:14:\"multi_category\";s:1:\"1\";s:11:\"enable_tags\";s:1:\"1\";s:9:\"poster_dx\";s:3:\"200\";s:9:\"poster_dy\";s:3:\"200\";s:10:\"gallery_dx\";s:3:\"150\";s:10:\"gallery_dy\";s:3:\"150\";}',	'news',	1,	0,	'',	''),
  (4,	'components',	'',	'sitemap',	1,	0,	'',	''),
  (5,	'components',	'',	'feedback',	1,	0,	'',	''),
  (6,	'components',	'',	'search',	1,	0,	'',	''),
  (8,	'hooks',	'a:3:{s:12:\"captcha_type\";s:8:\"ccaptcha\";s:17:\"captcha_publickey\";s:40:\"6Lf5V-YSAAAAAHjZXfPuyetxodstkHEkIn621OdE\";s:18:\"captcha_privatekey\";s:40:\"6Lf5V-YSAAAAACmTdU4Fd0uUbLTdMtI4rYGenl-X\";}',	'captcha',	1,	0,	'',	''),
  (9,	'hooks',	'',	'profile',	0,	0,	'',	''),
  (10,	'hooks',	'',	'bbtohtml',	1,	0,	'',	''),
  (11,	'hooks',	'',	'comment',	1,	0,	'',	''),
  (12,	'hooks',	'',	'file',	1,	0,	'',	''),
  (13,	'hooks',	'',	'mail',	1,	0,	'',	''),
  (15,	'modules',	'',	'news_on_main',	1,	1,	'index',	''),
  (16,	'modules',	'a:2:{s:7:\"news_id\";s:1:\"1\";s:9:\"show_date\";s:1:\"0\";}',	'static_on_main',	0,	1,	'index',	''),
  (17,	'modules',	'a:5:{s:14:\"comments_count\";s:1:\"5\";s:10:\"time_delay\";s:2:\"60\";s:9:\"edit_time\";s:2:\"30\";s:10:\"min_length\";s:2:\"10\";s:10:\"max_length\";s:4:\"2000\";}',	'comments',	1,	1,	'news/*;static/*;extension/*',	''),
  (18,	'modules',	'',	'usernotify',	1,	1,	'*',	''),
  (19,	'modules',	'a:2:{s:10:\"last_count\";s:1:\"5\";s:11:\"text_length\";s:2:\"70\";}',	'lastcomments',	1,	1,	'*',	''),
  (20,	'modules',	'a:3:{s:9:\"tag_count\";s:2:\"20\";s:22:\"template_position_name\";s:4:\"left\";s:23:\"template_position_index\";s:1:\"2\";}',	'tagcloud',	1,	1,	'*',	'');

DROP TABLE IF EXISTS `{$db_prefix}_user_access_level`;
CREATE TABLE `{$db_prefix}_user_access_level` (
  `group_id` int(12) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(12) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{$db_prefix}_user_access_level` (`group_id`, `group_name`, `permissions`) VALUES
  (1,	'User',	'global/read;global/write;comment/add'),
  (2,	'Moderator',	'global/read;global/write;comment/add;comment/edit;comment/delete;'),
  (3,	'Admin',	'global/read;global/write;comment/add;comment/edit;comment/delete;global/owner;'),
  (5,	'Banned',	''),
  (4,	'Only Read',	'global/read;');

DROP TABLE IF EXISTS `{$db_prefix}_components`;
DROP TABLE IF EXISTS `{$db_prefix}_modules`;
DROP TABLE IF EXISTS `{$db_prefix}_hooks`;

ALTER TABLE  `{$db_prefix}_com_news_entery` CHANGE  `title`  `title` VARCHAR( 2048 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `{$db_prefix}_com_news_entery` CHANGE  `description`  `description` VARCHAR( 2048 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `{$db_prefix}_com_news_entery` CHANGE  `keywords`  `keywords` VARCHAR( 4096 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `{$db_prefix}_com_news_category` ADD  `desc` VARCHAR( 4096 ) NOT NULL DEFAULT  '' AFTER  `name`;

UPDATE `{$db_prefix}_version` SET `version` = '2.0.0';