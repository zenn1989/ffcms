<?php

use engine\user;

class api_checkauth_back {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        echo (int)user::getInstance()->get('id');
        return null;
    }
}