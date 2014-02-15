<?php

use engine\user;
use engine\database;
use engine\property;
use engine\template;
use engine\system;

class modules_usernotify_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function make() {
        $this->showNewPmCount();
        $this->showNewFriendRequestCount();
    }

    private function showNewPmCount() {
        $userid = user::getInstance()->get('id');
        $lastpmview = user::getInstance()->get('lastpmview');
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_messages WHERE `to` = ? AND timeupdate >= ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->bindParam(2, $lastpmview, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $new_pm_count = $result[0];
        template::getInstance()->set(template::TYPE_MODULE, 'message_new_count', $new_pm_count);
    }

    private function showNewFriendRequestCount() {
        $friendRequestList = user::getInstance()->get('friend_request');
        $friend_array = system::getInstance()->altexplode(',', $friendRequestList);
        $request_count = sizeof($friend_array);
        template::getInstance()->set(template::TYPE_MODULE, 'friendrequest_new_count', $request_count);
    }
}