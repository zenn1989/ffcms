ALTER TABLE  `{$db_prefix}_com_news_category` ADD  `public` INT( 1 ) NOT NULL DEFAULT  '1';
CREATE TABLE IF NOT EXISTS `{$db_prefix}_mod_menu_ditem` (
  `d_id` int(24) NOT NULL AUTO_INCREMENT,
  `d_owner_gid` int(24) NOT NULL,
  `d_name` varchar(4096) NOT NULL,
  `d_url` varchar(4096) NOT NULL,
  `d_priority` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`d_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `{$db_prefix}_mod_menu_ditem` (`d_id`, `d_owner_gid`, `d_name`, `d_url`, `d_priority`) VALUES
(1, 3, 'a:2:{s:2:"en";s:5:"Forum";s:2:"ru";s:10:"Форум";}', 'http://ffcms.ru/en/forum/', 0);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_mod_menu_gitem` (
  `g_id` int(24) NOT NULL AUTO_INCREMENT,
  `g_menu_head_id` int(24) NOT NULL,
  `g_name` varchar(4096) NOT NULL,
  `g_url` varchar(4096) NOT NULL,
  `g_priority` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`g_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;


INSERT INTO `{$db_prefix}_mod_menu_gitem` (`g_id`, `g_menu_head_id`, `g_name`, `g_url`, `g_priority`) VALUES
(1, 1, 'a:2:{s:2:"en";s:4:"Home";s:2:"ru";s:14:"Главная";}', '/', 0),
(2, 1, 'a:2:{s:2:"en";s:13:"About website";s:2:"ru";s:13:"О сайте";}', '/static/about.html', 1),
(3, 1, 'a:2:{s:2:"en";s:13:"FFCMS Project";s:2:"ru";s:18:"Проект FFCMS";}', 'http://ffcms.ru', 2),
(4, 1, 'a:2:{s:2:"en";s:9:"Site news";s:2:"ru";s:25:"Новости сайта";}', '/news/', 3);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_mod_menu_header` (
  `menu_id` int(12) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(4096) NOT NULL,
  `menu_tag` varchar(128) NOT NULL,
  `menu_tpl` varchar(256) NOT NULL,
  `menu_display` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`menu_id`),
  UNIQUE KEY `menu_tag` (`menu_tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `{$db_prefix}_mod_menu_header` (`menu_id`, `menu_name`, `menu_tag`, `menu_tpl`, `menu_display`) VALUES
(1, 'a:2:{s:2:"en";s:4:"Menu";s:2:"ru";s:18:"Навигация";}', 'left', 'default.tpl', 1);

UPDATE `{$db_prefix}_version` SET `version` = '2.0.4';