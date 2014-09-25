ALTER TABLE `{$db_prefix}_user_custom` ADD `karma` int(24) NOT NULL DEFAULT '0';
CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_karma` (
  `trans_id` int(36) NOT NULL AUTO_INCREMENT,
  `from_id` int(36) NOT NULL,
  `to_id` int(36) NOT NULL,
  `type` int(2) NOT NULL,
  `date` int(18) NOT NULL,
  `from_ip` varchar(16) NOT NULL,
  `url` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`trans_id`),
  UNIQUE KEY `trans_id` (`trans_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `{$db_prefix}_com_stream` (
  `id` int(64) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) DEFAULT NULL,
  `caster_id` varchar(40) NOT NULL,
  `target_object` varchar(1024) NOT NULL,
  `text_preview` varchar(1024) DEFAULT NULL,
  `date` int(16) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `{$db_prefix}_extensions` (`type`, `configs`, `dir`, `enabled`, `path_choice`, `path_allow`, `path_deny`, `version`, `compatable`) VALUES ('components', 'a:1:{s:17:"count_stream_page";s:2:"20";}', 'stream', 0, 1, '*', '', '1.0.1', '2.0.2');