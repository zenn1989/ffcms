<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

// system are not installed or file is missed
if(!file_exists("config.php")) {
    if(loader !== 'install')
        exit();
} else {
    require_once(root . '/config.php');
}
// default timezone from configs
date_default_timezone_set(\engine\property::getInstance()->get('time_zone'));

\engine\property::getInstance()->init(); // processing of URI for multi-language and friendly url's
\engine\language::getInstance()->init();
\engine\router::getInstance()->init();

// check ip/user is fully blocked?
\engine\ban::getInstance()->init();
\engine\user::getInstance()->init();
\engine\api::getInstance()->make(); // echo enteries