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
        exit("System are not installed or file config.php is missed. Run <a href='/install/'>Installer</a>.");
} else {
    require_once(root . '/config.php');
}

// singleton abstraction & sys configs
require_once(root . '/engine/singleton.class.php');
require_once(root . '/engine/property.class.php');

// default timezone from configs
date_default_timezone_set(\engine\property::getInstance()->get('time_zone'));

// other system class & api
require_once(root . '/engine/template.class.php');
require_once(root . '/engine/router.class.php');
require_once(root . '/engine/system.class.php');
require_once(root . '/engine/logger.class.php');
require_once(root . '/engine/language.class.php');
require_once(root . '/engine/database.class.php');
require_once(root . '/engine/user.class.php');
require_once(root . '/engine/extension.class.php');
require_once(root . '/engine/meta.class.php');
require_once(root . '/engine/robot.class.php');
require_once(root . '/engine/permission.class.php');
require_once(root . '/engine/cache.class.php');
require_once(root . '/engine/ban.class.php');
require_once(root . '/engine/admin.class.php');
require_once(root . '/engine/antivirus.class.php');
require_once(root . '/engine/dumper.class.php');

\engine\property::getInstance()->dymanicPrepares(); // processing of URI for multi-language and friendly url's
\engine\template::getInstance()->twigDefaultVariables(); // set default template variables according changes in dymanic variables
\engine\template::getInstance()->twigUserVariables(); // user default variables init

echo \engine\admin::getInstance()->make();