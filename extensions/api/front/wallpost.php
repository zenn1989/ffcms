<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\database;
use engine\property;
use engine\system;
use engine\extension;
use engine\user;
use engine\api;
use engine\permission;

class api_wallpost_front extends \engine\singleton {

    public function make() {
        $post_id = (int)system::getInstance()->get('id');
        $user_id = (int)user::getInstance()->get('id');
        $message = system::getInstance()->nohtml(system::getInstance()->post('message')); // thank unknown tester for detect XSS vuln
        $time_between_posts = extension::getInstance()->getConfig('wall_post_delay', 'user', 'components', 'int');
        if($post_id > 0 && $user_id > 0 && system::getInstance()->length($message) > 0 && permission::getInstance()->have('global/write')) {
            $stmt = database::getInstance()->con()->prepare("SELECT time FROM ".property::getInstance()->get('db_prefix')."_user_wall_answer WHERE poster = ? ORDER BY id DESC LIMIT 1");
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch();
            $last_post_time = $res['time'];
            $stmt = null;
            $current_time = time();
            if(($current_time - $last_post_time) >= $time_between_posts) {
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_wall_answer (wall_post_id, poster, message, time) VALUES(?, ?, ?, ?)");
                $stmt->bindParam(1, $post_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
                $stmt->bindParam(3, $message, PDO::PARAM_STR);
                $stmt->bindParam(4, $current_time, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        }
        api::getInstance()->call('front', 'wallview')->make(); // refresh list
    }
}