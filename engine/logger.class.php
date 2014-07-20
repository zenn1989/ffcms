<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class logger extends singleton {
    const LEVEL_ERR = 'Error';
    const LEVEL_WARN = 'Warning';
    const LEVEL_NOTIFY = 'Notify';

    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            system::getInstance()->createPrivateDirectory(root . '/log/');
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function log($type, $message) {
        $iface = defined('loader') ? loader : 'unknown';
        $compile_message = "=>[".$iface.":".$type."](".system::getInstance()->toDate(time(), 's')."): ".$message."\n";
        @file_put_contents(root . "/log/".system::getInstance()->toDate(time(), 'd').".log", $compile_message, FILE_APPEND | LOCK_EX);
    }
}



?>