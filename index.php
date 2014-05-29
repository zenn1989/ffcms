<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */
session_start();
$debug_starttime = microtime(true);
define('root', dirname(__FILE__));
define('loader', 'front');


require_once(root . "/load.php");