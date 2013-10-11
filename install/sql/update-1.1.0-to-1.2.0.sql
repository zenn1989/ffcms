ALTER TABLE  `{$db_prefix}_mod_comments` ADD  `pathway` VARCHAR( 256 ) NOT NULL;
ALTER TABLE  `{$db_prefix}_user` ADD  `balance` DECIMAL( 12, 2 ) NOT NULL DEFAULT  '0.00';