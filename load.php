<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */
error_reporting(E_ERROR);

define('version', '2.0.3');

// load test - single thread * 10 times
// -- type -- | nocache time | cache time | nocache ram | cache ram |
//    static  | 0,96 - 0,99s | 0,12-0,17s |  6,8-7mb    |   4,452m  |
//    dynamic | 0,95 - 1,01s | 0,12-0,175s|  6,8-7mb    |   4,467m  |
// must have - no difference > 3% between tests

class load {
    public static function _init() {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'autoload'));
    }

    protected static function autoload($class) {
        $pathname = root . '/';
        $pathname .= str_replace("\\", "/", $class);
        $pathname .= ".class.php";
        if(is_readable($pathname))
            require_once($pathname);
    }
}
load::_init();
// init main methods after autoload - prepare userdata,templates,router,statistic
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