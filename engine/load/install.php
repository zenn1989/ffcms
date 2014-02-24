<?php

// singleton abstraction & sys configs
require_once(root . '/engine/singleton.class.php');
if(file_exists(root . '/config.php')) // mb its a update action?
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
require_once(root . '/engine/cache.class.php');
require_once(root . '/engine/install.class.php');

\engine\template::getInstance()->twigDefaultVariables(); // set default template variables according changes in dymanic variables

\engine\install::getInstance()->make(); // prepare data
echo \engine\template::getInstance()->make(); // output main tpl