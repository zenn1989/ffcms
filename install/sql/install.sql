SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `{$db_prefix}_components` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `configs` text NOT NULL,
  `dir` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

INSERT INTO `{$db_prefix}_components` VALUES
(1, '', 'static', 1),
(2, 'a:10:{s:13:"login_captcha";s:1:"0";s:16:"register_captcha";s:1:"1";s:15:"register_aprove";s:1:"0";s:12:"profile_view";s:1:"1";s:15:"wall_post_count";s:1:"5";s:16:"marks_post_count";s:1:"5";s:17:"friend_page_count";s:2:"20";s:15:"wall_post_delay";s:2:"30";s:8:"pm_count";s:1:"5";s:14:"userlist_count";s:2:"10";}', 'usercontrol', 1),
(3, 'a:5:{s:17:"delay_news_public";s:1:"1";s:15:"count_news_page";s:1:"5";s:17:"short_news_length";s:3:"200";s:14:"multi_category";s:1:"1";s:11:"enable_tags";s:1:"1";}', 'news', 1),
(6, '', 'sitemap', 1),
(7, '', 'feedback', 1),
(8, '', 'search', 1);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_com_feedback` (
  `id` int(36) NOT NULL AUTO_INCREMENT,
  `from_name` varchar(128) NOT NULL,
  `from_email` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  `text` text NOT NULL,
  `time` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO `{$db_prefix}_com_feedback` VALUES
(1, 'Mihail', 'ffcms@yandex.ru', 'Test message on feedback', 'This message is just a test generated by ffcms system.\r\nThanks for installing our system.', 1374667785);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_com_news_category` (
  `category_id` int(24) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `path` varchar(320) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `link` (`path`),
  KEY `id` (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

INSERT INTO `{$db_prefix}_com_news_category` VALUES
(1, 'a:2:{s:2:"en";s:4:"Main";s:2:"ru";s:14:"Главная";}', '');

CREATE TABLE IF NOT EXISTS `{$db_prefix}_com_news_entery` (
  `id` int(24) NOT NULL AUTO_INCREMENT,
  `title` varchar(512) NOT NULL,
  `text` text NOT NULL,
  `link` varchar(256) NOT NULL,
  `category` int(24) NOT NULL,
  `date` int(16) NOT NULL,
  `author` int(24) NOT NULL,
  `description` varchar(250) NOT NULL,
  `keywords` varchar(250) NOT NULL,
  `views` int(36) NOT NULL DEFAULT '0',
  `display` int(2) NOT NULL DEFAULT '1',
  `important` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  FULLTEXT KEY `title` (`title`,`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `{$db_prefix}_com_news_entery` VALUES
(1, 'a:2:{s:2:"en";s:41:"Fast Flexibility content managment system";s:2:"ru";s:56:"FFCMS - система управления сайтом";}', 'a:2:{s:2:"en";s:1855:"\r\n<p style="text-align: center;"><img src="http://ffcms/upload/defaults/ffcms-box.png" title="ffcms box logo" alt="ffcms logo" style="border: 0px rgb(0, 0, 0); width: 279px; height: 300px; cursor: default;"></p>\r\n\r\n<p>FFCMS - fast flexibility content managment system. FFcms is a free content management writed on PHP5 using database mysql 5.&nbsp;</p>\r\n<hr />\r\n<p>FFCMS contains 3 type of extensions:</p>\r\n\r\n<ul>\r\n	<li>Components</li>\r\n	<li>Modules</li>\r\n	<li>Hooks</li>\r\n</ul><div><div><b>1. Components</b>&nbsp;- provide an implementation of the main content of the page (or multiple pages) at a specific url . FFCMS include 6 default components - News, Static pages, Usercontrol, Sitemap generator, Search engine, Feedback that implement the basic functionality of the site required for personal blogs, portals and corporate pages. \r\n<p><b>2. Modules</b>&nbsp;- provide an implementation of a particular position on the entire website or parts of it. In default package&nbsp;ffcms included modules:&nbsp;&nbsp;user comments, statick blocks, news and static pages on main page website and other modules.&nbsp;</p>\r\n\r\n<p><b>3. Hooks</b>&nbsp;- provide an implementation of some of the patterns of interaction with the site core modules and components.As example of hooks is realisation of captcha,&nbsp;methods of calculating comments count for objects, parsing&nbsp;bbcode, extending user profiles and other.&nbsp;</p>\r\n\r\n<p>FFCMS - its a free system realised under GNU GPL v3 license. System can be used on all projects if copyrights of author be saved.&nbsp;</p>\r\n\r\n<p>Official website:&nbsp;<a href="http://ffcms.ru/" title="ffcms website" target="_blank">www.ffcms.ru</a></p>\r\n\r\n<p>Project on github:&nbsp;<a href="https://github.com/zenn1989/ffcms" title="git repository" target="_blank">zenn1989/ffcms/</a></p>\r\n<br>\r\n</div>\r\n</div>\r\n";s:2:"ru";s:3232:"\r\n<p style="text-align: center;"><img src="/upload/defaults/ffcms-box.png" style="border: 0px rgb(0, 0, 0); width: 279px; height: 300px; cursor: default;" title="ffcms box logo" alt="ffcms logo"></p>\r\n\r\n<p>FFCMS - быстрая и расширяемая система управления содержимым сайта. Наша система написана с использованием php5 и баз данных mysql.&nbsp;</p>\r\n<hr />\r\n<p>FFCMS содержит 3 типа расширений:</p>\r\n\r\n<ul>\r\n	<li>Компоненты</li>\r\n	<li>Модули</li>\r\n	<li>Хуки</li>\r\n</ul><div><b>1. Компоненты</b> - предоставляют реализацию основного содержимого страницы(или же нескольких страниц) при определенном url. В систему ffcms по стандарту включено 6 основных модулей: Новости, Статические страницы, Идентификация пользователя, Поиск по сайту, Карта сайта, Обратная связь которые реализуют основной необходимый функционал сайта для личных блогов, порталов и корпоративных страничек. \r\n<p><b>2. Модули</b> - предоставляют реализацию определенной позиции на всем сайте или его частях. В стандартной комплектации ffcms включены такие модули как Комментарии пользователей, Статические блоки, Вывод новостей и статических страниц на главную а так же другие.&nbsp;</p>\r\n\r\n<p><b>3. Хуки</b> - предоставляют реализацию некоторых моделей взаимодействия ядра сайта с модулями и компонентами. Наглядным примером хука(включения) является реализация капчи, методов подсчета количества комментариев к тому или иному объекту, преобразование bbcode, дополнение профиля пользователя&nbsp;и прочие.&nbsp;</p>\r\n\r\n<p>Система FFCMS является абсолютно бесплатной и распространяется по лицензии GNU GPL V3, содержимое которой вы можете найти в корне вашего сайта. Система может использоваться на любых сайтах(коммерческой и не коммерческой деятельности) с учетом сохранения авторских прав создателя системы.&nbsp;</p>\r\n\r\n<p>Официальный сайт системы: <a href="http://ffcms.ru" title="ffcms website" target="_blank">www.ffcms.ru</a></p>\r\n\r\n<p>Проект на github: <a href="https://github.com/zenn1989/ffcms" title="git repository" target="_blank">zenn1989/ffcms/</a></p>\r\n</div>\r\n";}', 'demo-ffcms.html', 1, 1373210580, 1, 'a:2:{s:2:"en";s:38:"FFCMS - free content management system";s:2:"ru";s:75:"FFCMS - система управления содержимым сайта";}', 'a:2:{s:2:"en";s:35:"ffcms, free, cms, fast, flexibility";s:2:"ru";s:34:"ffcms, cms, fast, flexibility, php";}', 172, 1, 0);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_com_static` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `text` text NOT NULL,
  `owner` int(32) NOT NULL,
  `pathway` varchar(256) NOT NULL,
  `date` int(16) NOT NULL,
  `description` text NOT NULL,
  `keywords` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `pathway_2` (`pathway`),
  KEY `pathway` (`pathway`),
  KEY `pathway_3` (`pathway`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `{$db_prefix}_com_static` VALUES
(1, 'a:2:{s:2:"en";s:13:"About example";s:2:"ru";s:13:"О сайте";}', 'a:2:{s:2:"en";s:129:"<div> This page provide example of about page. \r\n<p>You can modife it in admin panel-&gt;components-&gt;static pages.</p>\r\n</div>";s:2:"ru";s:343:"<div>Данная страница является демонстрационной и демонстрирует работу системы ffcms.</div><div>Вы можете изменить данную страницу в административной панели->компоненты->Статические страницы.</div>";}', 1, 'about.html', 1373230800, 'a:2:{s:2:"en";s:0:"";s:2:"ru";s:0:"";}', 'a:2:{s:2:"en";s:0:"";s:2:"ru";s:0:"";}');

CREATE TABLE IF NOT EXISTS `{$db_prefix}_hooks` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `configs` text NOT NULL,
  `dir` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'other',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `{$db_prefix}_hooks` VALUES
(1, '', 'captcha', 'captcha', 1),
(2, '', 'profile', 'profile', 0),
(3, '', 'bbtohtml', 'bbtohtml', 1),
(4, '', 'comment', 'comment', 1);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_modules` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `configs` text NOT NULL,
  `dir` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `path_choice` tinyint(1) NOT NULL DEFAULT '0',
  `path_allow` varchar(256) NOT NULL,
  `path_deny` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

INSERT INTO `{$db_prefix}_modules` VALUES
(1, '', 'static_includes', 1, 1, '*', ''),
(2, '', 'news_on_main', 1, 1, 'index', ''),
(3, 'a:2:{s:7:"news_id";s:1:"2";s:9:"show_date";s:1:"1";}', 'static_on_main', 0, 1, 'index', ''),
(4, 'a:5:{s:14:"comments_count";s:1:"5";s:10:"time_delay";s:2:"60";s:9:"edit_time";s:2:"30";s:10:"min_length";s:2:"10";s:10:"max_length";s:4:"2000";}', 'comments', 1, 1, 'news/*;static/*', ''),
(5, '', 'user_notify', 1, 1, '*', '');

CREATE TABLE IF NOT EXISTS `{$db_prefix}_mod_comments` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `target_hash` varchar(32) NOT NULL,
  `object_name` varchar(64) NOT NULL,
  `object_id` int(32) NOT NULL,
  `comment` varchar(512) NOT NULL,
  `author` int(32) NOT NULL,
  `time` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `{$db_prefix}_mod_comments` VALUES
(1, 'bf31ebf1a2d0c2f3c730521a0b2d2acd', 'news', 1, 'The first test comment from ffcms :)', 1, 1375001386);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_statistic` (
  `id` int(64) NOT NULL AUTO_INCREMENT,
  `ip` varchar(24) NOT NULL,
  `cookie` varchar(32) NOT NULL DEFAULT '0',
  `browser` varchar(64) NOT NULL,
  `os` varchar(16) NOT NULL,
  `time` int(32) NOT NULL,
  `referer` varchar(256) NOT NULL DEFAULT '0',
  `path` varchar(128) NOT NULL,
  `reg_id` int(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user` (
  `id` int(36) NOT NULL AUTO_INCREMENT,
  `login` varchar(128) NOT NULL,
  `email` varchar(256) NOT NULL,
  `nick` varchar(36) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `access_level` int(2) NOT NULL DEFAULT '1',
  `token` varchar(32) NOT NULL,
  `token_start` int(16) NOT NULL,
  `aprove` varchar(32) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_access_level` (
  `group_id` int(12) NOT NULL,
  `group_name` varchar(12) NOT NULL,
  `access_to_admin` int(2) NOT NULL DEFAULT '0',
  `content_view` int(2) NOT NULL DEFAULT '1',
  `content_post` int(2) NOT NULL DEFAULT '1',
  `mod_comment_add` int(11) NOT NULL DEFAULT '0',
  `mod_comment_edit` int(11) NOT NULL DEFAULT '0',
  `mod_comment_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{$db_prefix}_user_access_level` VALUES
(1, 'User', 0, 1, 1, 1, 0, 0),
(2, 'Moderator', 0, 1, 1, 1, 1, 1),
(3, 'Admin', 1, 1, 1, 1, 1, 1),
(0, 'Banned', 0, 0, 0, 0, 0, 0),
(4, 'Only Read', 0, 1, 0, 0, 0, 0),
(5, 'Only Post', 0, 0, 1, 1, 0, 0);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_block` (
  `id` int(36) NOT NULL AUTO_INCREMENT,
  `user_id` int(24) NOT NULL DEFAULT '0',
  `ip` varchar(24) NOT NULL,
  `express` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_bookmarks` (
  `id` int(24) NOT NULL AUTO_INCREMENT,
  `target` int(24) NOT NULL,
  `title` varchar(256) NOT NULL,
  `href` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_custom` (
  `id` int(24) NOT NULL,
  `birthday` date NOT NULL DEFAULT '0000-00-00',
  `regdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sex` tinyint(2) NOT NULL DEFAULT '0',
  `phone` varchar(16) NOT NULL,
  `friend_list` text NOT NULL,
  `friend_request` text NOT NULL,
  `status` varchar(128) NOT NULL,
  `webpage` varchar(128) NOT NULL,
  `lastpmview` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_messages` (
  `id` int(36) NOT NULL AUTO_INCREMENT,
  `from` int(32) NOT NULL,
  `to` int(32) NOT NULL,
  `message` varchar(512) NOT NULL,
  `timeupdate` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_messages_answer` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `topic` int(32) NOT NULL,
  `from` int(32) NOT NULL,
  `message` varchar(512) NOT NULL,
  `time` int(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_recovery` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `password` varchar(32) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `userid` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_wall` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `target` int(32) NOT NULL,
  `caster` int(32) NOT NULL,
  `message` varchar(256) NOT NULL,
  `time` int(24) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `{$db_prefix}_user_wall` VALUES
(1, 1, 1, 'Demo wall post from ffcms', 1375004751);

CREATE TABLE IF NOT EXISTS `{$db_prefix}_user_wall_answer` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `wall_post_id` int(32) NOT NULL,
  `poster` int(32) NOT NULL,
  `message` varchar(256) NOT NULL,
  `time` int(24) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$db_prefix}_version` (
  `version` varchar(24) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `{$db_prefix}_version` VALUES
('1.0.0');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;