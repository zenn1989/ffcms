<?php
define('root', dirname(__FILE__));
define('loader', 'api');
require_once(root . '/load.php');
require_once(root . '/engine/api.class.php');

\engine\language::getInstance();
\engine\user::getInstance();
\engine\extension::getInstance()->loadModules();
\engine\api::getInstance()->make(); // echo enteries





