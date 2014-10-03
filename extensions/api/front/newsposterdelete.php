<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\user;
use engine\system;
use engine\extension;

class api_newsposterdelete_front extends \engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $id = (int)system::getInstance()->get('id');
        $user_id = user::getInstance()->get('id');
        if($user_id < 1 || $id < 1 || !extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'bol') || !extension::getInstance()->call(extension::TYPE_COMPONENT, 'news')->checkNewsOwnerExist($user_id, $id))
            return;
        $fpath = root . '/upload/news/poster_' . $id . '.jpg';
        if(file_exists($fpath))
            @unlink($fpath);
    }
}