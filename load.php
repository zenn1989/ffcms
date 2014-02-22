<?php

define('version', '2.0.0');

// singleton abstraction & sys configs
require_once(root . '/engine/singleton.class.php');
require_once(root . '/config.php');
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

\engine\router::getInstance()->prepareRoutes(); // prepare URI worker
\engine\property::getInstance()->dymanicPrepares(); // processing of URI for multi-language and friendly url's
\engine\template::getInstance()->twigDefaultVariables(); // set default template variables according changes in dymanic variables