<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */
error_reporting(E_ERROR);

define('version', '2.0.0');

switch(loader) {
    case 'front':
        require_once(root . '/engine/load/front.php');
        break;
    case 'back':
        require_once(root . '/engine/load/back.php');
        break;
    case 'api':
        require_once(root . '/engine/load/api.php');
        break;
    case 'install':
        require_once(root . '/engine/load/install.php');
        break;
}