<?php

use engine\user;
use engine\property;
use engine\database;
use engine\permission;
use engine\system;

class api_commentdelete_front {
    protected static $instance = null;
    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $comment_id = (int)system::getInstance()->get('id');
        if(user::getInstance()->get('id') > 0 && permission::getInstance()->have('comment/delete') && $comment_id > 0) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}