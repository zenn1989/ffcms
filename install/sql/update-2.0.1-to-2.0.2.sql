ALTER TABLE  `{$db_prefix}_extensions` ADD  `version` VARCHAR( 12 ) NULL DEFAULT NULL , ADD  `compatable` VARCHAR( 12 ) NULL DEFAULT NULL ;
UPDATE `{$db_prefix}_extensions` SET `version` = '1.0.1', `compatable` = '2.0.2' WHERE `dir` IN ('static', 'user', 'news', 'sitemap', 'feedback', 'search',
'captcha', 'profile', 'bbtohtml', 'comment', 'file', 'mail', 'news_on_main', 'static_on_main',
'comments', 'usernotify', 'lastcomments', 'tagcloud', 'news_top_discus', 'news_top_view', 'news_new');
UPDATE `{$db_prefix}_version` SET `version` = '2.0.2';