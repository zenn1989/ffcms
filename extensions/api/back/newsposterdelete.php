<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\permission;
use engine\system;

class api_newsposterdelete_back extends \engine\singleton {

    public function make() {
        if(!permission::getInstance()->have('admin/components/news/add') && !permission::getInstance()->have('admin/components/news/edit'))
            return;
        $id = (int)system::getInstance()->get('id');
        $fpath = root . '/upload/news/poster_' . $id . '.jpg';
        if(file_exists($fpath))
            @unlink($fpath);
    }
}