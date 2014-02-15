<?php
// admin iface marker
define('loader', 'back');
define('root', dirname(__FILE__));


require_once(root . '/load.php');
require_once(root . '/engine/admin.class.php');
require_once(root . '/engine/antivirus.class.php');
require_once(root . '/engine/dumper.class.php');

echo \engine\admin::getInstance()->make();
