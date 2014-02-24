<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
array_pop($path_array);
define('root', implode(DIRECTORY_SEPARATOR, $path_array));
define('loader', 'install');

require_once(root . '/load.php');
