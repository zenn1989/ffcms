<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */
// admin iface marker
session_start();
define('loader', 'back');
define('root', dirname(__FILE__));

require_once(root . '/load.php');
