<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class ban extends singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Check if user is permament banned in database and display ban.tpl theme
     */
    public function init() {
        $ip = system::getInstance()->getRealIp();
        $time = time();
        $userid = user::getInstance()->get('id');
        if ($userid > 0) {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_block WHERE (user_id = ? or ip = ?) AND (express > ? OR express = 0)");
            $stmt->bindParam(1, $userid, \PDO::PARAM_INT);
            $stmt->bindParam(2, $ip, \PDO::PARAM_STR);
            $stmt->bindParam(3, $time, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user_block WHERE ip = ? AND (express > ? OR express = 0)");
            $stmt->bindParam(1, $ip, \PDO::PARAM_STR);
            $stmt->bindParam(2, $time, \PDO::PARAM_INT);
            $stmt->execute();
        }
        $rowFetch = $stmt->fetch();
        $count = $rowFetch[0];
        if($count > 0) { // block founded in db
            $content = template::getInstance()->twigRender('ban.tpl', array('local' => array('admin_email' => property::getInstance()->get('mail_from'))));
            template::getInstance()->justPrint($content);
        }
    }
}