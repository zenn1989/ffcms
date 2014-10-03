<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\user;
use engine\extension;
use engine\database;
use engine\property;
use engine\permission;

class api_commentpost_front extends \engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function make() {
        $this->postComment();
    }

    private function postComment() {
        $text = system::getInstance()->nohtml(system::getInstance()->post('comment_message'), true);
        $authorid = user::getInstance()->get('id');
        $position = system::getInstance()->post('comment_position');
        $pathway = system::getInstance()->post('pathway');
        $guest_name = system::getInstance()->nohtml(system::getInstance()->post('guest_name'));
        $timestamp = time();
        $guest_type = false;
        $ip = system::getInstance()->getRealIp();
        $params = array();
        $moderate = 0;
        if($authorid < 1) {
            if(system::getInstance()->length($guest_name) > 0 && extension::getInstance()->getConfig('guest_comment', 'comments', extension::TYPE_MODULE, 'bool')) {
                $guest_name = system::getInstance()->altsubstr($guest_name, 0, 16);
                if (!extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate(system::getInstance()->post('captcha'))) {
                    $params['notify']['captcha_error'] = true;
                }
            } elseif(!permission::getInstance()->have('global/write') || !permission::getInstance()->have('comment/add')) { // only for auth usr with post rule right
                return null;
            }
            $authorid = 0;
            $moderate = 1;
        } else {
            $guest_name = '';
        }
        if (system::getInstance()->length($text) < extension::getInstance()->getConfig('min_length', 'comments', 'modules', 'int') || system::getInstance()->length($text) > extension::getInstance()->getConfig('max_length', 'comments', 'modules', 'int')) {
            $params['notify']['wrong_text'] = true;
        }
        // get last comment from this user and check time deps
        $stmt = null;
        if($guest_type) {
            $stmt = database::getInstance()->con()->prepare("SELECT `time` FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE ip = ? ORDER BY `time` DESC LIMIT 1");
            $stmt->bindParam(1, $ip, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT `time` FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE author = ? ORDER BY `time` DESC LIMIT 1");
            $stmt->bindParam(1, $authorid, PDO::PARAM_INT);
            $stmt->execute();
        }
        if($stmt != null && $result = $stmt->fetch()) {
            $lastposttime = $result['time'];
            if (($timestamp - $lastposttime) < extension::getInstance()->getConfig('time_delay', 'comments', 'modules', 'int')) {
                $params['notify']['time_delay'] = true;
            }
        }
        $stmt = null;
        if(sizeof($params['notify']) == 0) { // no shit happends ;D
            $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_comments (comment, author, time, pathway, ip, guest_name, moderate)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bindParam(1, $text, PDO::PARAM_STR);
            $stmt->bindParam(2, $authorid, PDO::PARAM_INT);
            $stmt->bindParam(3, $timestamp, PDO::PARAM_INT);
            $stmt->bindParam(4, $pathway, PDO::PARAM_STR);
            $stmt->bindParam(5, $ip, PDO::PARAM_STR);
            $stmt->bindParam(6, $guest_name, PDO::PARAM_STR);
            $stmt->bindParam(7, $moderate, PDO::PARAM_INT, 1);
            $stmt->execute();
            $stmt = null;
            $stream = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream');
            $poster = $authorid > 0 ? $authorid : $guest_name;
            if(is_object($stream))
                $stream->add('comment.add', $poster, property::getInstance()->get('url').$pathway, $text);
            if($moderate) {
                $params['notify']['is_moderate'] = true;
            }
        }
        echo extension::getInstance()->call(extension::TYPE_MODULE, 'comments')->buildCommentTemplate($pathway, $position, false, $params);
    }

}


?>