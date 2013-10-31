ALTER TABLE  `{$db_prefix}_mod_comments` ADD  `pathway` VARCHAR( 256 ) NOT NULL;
ALTER TABLE  `{$db_prefix}_user` ADD  `balance` DECIMAL( 12, 2 ) NOT NULL DEFAULT  '0.00';
CREATE TABLE  IF NOT EXISTS `{$db_prefix}_user_log` (
`id` INT( 36 ) NOT NULL AUTO_INCREMENT ,
`owner` INT( 36 ) NOT NULL ,
`type` VARCHAR( 32 ) NOT NULL ,
`params` TEXT NOT NULL ,
`message` VARCHAR( 256 ) NOT NULL ,
`time` INT( 16 ) NOT NULL,
PRIMARY KEY (  `id` ) ,
INDEX (  `id` )
) ENGINE = MYISAM ;
UPDATE `{$db_prefix}_components` SET `configs` = 'a:12:{s:13:"login_captcha";s:1:"0";s:16:"register_captcha";s:1:"1";s:15:"register_aprove";s:1:"1";s:10:"use_openid";s:1:"1";s:12:"profile_view";s:1:"1";s:15:"wall_post_count";s:1:"5";s:16:"marks_post_count";s:1:"5";s:17:"friend_page_count";s:2:"20";s:15:"wall_post_delay";s:2:"30";s:8:"pm_count";s:1:"5";s:12:"balance_view";s:1:"1";s:14:"userlist_count";s:2:"10";}' WHERE `dir` = 'usercontrol';
CREATE TABLE IF NOT EXISTS `{$db_prefix}_mod_tags` (
  `id` INT(32) NOT NULL AUTO_INCREMENT,
  `object_type` VARCHAR( 128 ) NOT NULL,
  `object_id` INT( 32 ) NOT NULL,
  `tag` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `object_type` (`object_type`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM ;
INSERT INTO `{$db_prefix}_modules` (`configs`, `dir`, `enabled`, `path_choice`, `path_allow`, `path_deny`) VALUES
('a:4:{s:10:"last_count";s:1:"5";s:11:"text_length";s:2:"70";s:22:"template_position_name";s:4:"left";s:23:"template_position_index";s:1:"1";}', 'lastcomments', 1, 1, '*', ''),
('a:3:{s:9:"tag_count";s:2:"25";s:22:"template_position_name";s:4:"left";s:23:"template_position_index";s:1:"2";}', 'tagcloud', 1, 1, '*', '');
UPDATE `{$db_prefix}_version` SET `version` = '1.2.0';