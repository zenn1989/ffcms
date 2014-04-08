<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\extension;
use engine\database;
use engine\property;
use engine\user;
use engine\system;
use engine\template;

class modules_lastcomments_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $comment_count = extension::getInstance()->getConfig('last_count', 'lastcomments', 'modules', 'int');
        if($comment_count < 1)
            $comment_count = 1;
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE `pathway` != '' ORDER BY `time` DESC LIMIT 0,?");
        $stmt->bindParam(1, $comment_count, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        if(sizeof($res) > 0) {
            // have comments in db
            $max_comment_char_size = extension::getInstance()->getConfig('text_length', 'lastcomments', 'modules', 'int');
            $prepared_userlist = system::getInstance()->extractFromMultyArray('author', $res);
            user::getInstance()->listload($prepared_userlist);
            $params = array();
            foreach($res as $result) {
                $comment_text = extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml')->nobbcode($result['comment']);
                $params['comment'][] = array(
                    'user_id' => $result['author'],
                    'user_name' => user::getInstance()->get('nick', $result['author']),
                    'user_avatar' => user::getInstance()->buildAvatar('small', $result['author']),
                    'uri' => $result['pathway'],
                    'preview' => system::getInstance()->altsubstr($comment_text, 0, $max_comment_char_size),
                    'date' => system::getInstance()->toDate($result['time'], 'd')
                );
            }
            $render = template::getInstance()->twigRender('modules/lastcomments/lastcomments.tpl', array('local' => $params));
            template::getInstance()->set(template::TYPE_MODULE, 'lastcomments', $render);
        }
    }
}