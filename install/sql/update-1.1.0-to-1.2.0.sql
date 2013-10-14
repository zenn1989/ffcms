ALTER TABLE  `{$db_prefix}_mod_comments` ADD  `pathway` VARCHAR( 256 ) NOT NULL;
ALTER TABLE  `{$db_prefix}_user` ADD  `balance` DECIMAL( 12, 2 ) NOT NULL DEFAULT  '0.00';
CREATE TABLE  `{$db_prefix}_user_log` (
`id` INT( 36 ) NOT NULL AUTO_INCREMENT ,
`owner` INT( 36 ) NOT NULL ,
`type` VARCHAR( 32 ) NOT NULL ,
`params` TEXT NOT NULL ,
`message` VARCHAR( 256 ) NOT NULL ,
`time` INT( 16 ) NOT NULL,
PRIMARY KEY (  `id` ) ,
INDEX (  `id` )
) ENGINE = MYISAM ;