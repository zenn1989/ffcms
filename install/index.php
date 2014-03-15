<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
array_pop($path_array);
define('root', implode(DIRECTORY_SEPARATOR, $path_array));
define('loader', 'install');

require_once(root . '/load.php');
