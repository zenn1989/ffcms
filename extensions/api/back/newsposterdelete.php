<?php

use engine\permission;
use engine\system;

class api_newsposterdelete_back {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        if(!permission::getInstance()->have('global/owner'))
            return;
        $id = (int)system::getInstance()->get('id');
        $fpath = root . '/upload/news/poster_' . $id . '.jpg';
        if(file_exists($fpath))
            @unlink($fpath);
    }
}