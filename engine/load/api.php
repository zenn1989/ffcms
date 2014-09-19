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

\engine\property::getInstance()->dymanicPrepares(); // processing of URI for multi-language and friendly url's
\engine\template::getInstance()->twigDefaultVariables(); // set default template variables according changes in dymanic variables
\engine\template::getInstance()->twigUserVariables(); // user default variables init

// check ip/user is fully blocked?
\engine\ban::getInstance()->check();

\engine\language::getInstance();
\engine\user::getInstance();
\engine\api::getInstance()->make(); // echo enteries