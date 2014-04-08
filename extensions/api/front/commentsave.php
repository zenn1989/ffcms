<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */
use engine\api;
use engine\system;
use engine\database;
use engine\property;
use engine\user;

class api_commentsave_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $obj = api::getInstance()->call('front', 'commentedit');
        if(is_object($obj)) {
            $comment_id = (int)system::getInstance()->post('comment_id');
            if(!$obj->canEdit($comment_id)) {
                return null;
            }
            $comment_text = system::getInstance()->nohtml(system::getInstance()->post('comment_text'), true);
            if($comment_id > 0 && strlen($comment_text) > 0) {
                $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_mod_comments set comment = ? where id = ?");
                $stmt->bindParam(1, $comment_text, PDO::PARAM_STR);
                $stmt->bindParam(2, $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        }
    }
}