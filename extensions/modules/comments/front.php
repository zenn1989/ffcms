<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\user;
use engine\system;
use engine\extension;
use engine\router;
use engine\database;
use engine\property;
use engine\template;
use engine\permission;

class modules_comments_front extends \engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Get comment list
     * @param null $way
     * @param int $end
     * @param bool $show_all
     * @return array
     */
    public function getCommentsParams($way = null, $end = 0, $show_all = false) {
        $userid = user::getInstance()->get('id');
        $stmt = null;
        if(is_null($way))
            $way = router::getInstance()->getUriString();
        if($show_all) {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE pathway = ? AND moderate = '0' ORDER BY id DESC");
            $stmt->bindParam(1, $way, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $comment_count = extension::getInstance()->getConfig('comments_count', 'comments', 'modules', 'int');
            if($end < 1) {
                $end = 1;
            }
            $end *= $comment_count;
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE pathway = ? AND moderate = '0' ORDER BY id DESC LIMIT 0,?");
            $stmt->bindParam(1, $way, PDO::PARAM_STR);
            $stmt->bindParam(2, $end, PDO::PARAM_INT);
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        user::getInstance()->listload(system::getInstance()->extractFromMultyArray('author', $result));
        $params = array();
        foreach ($result as $item) {
            $poster_id = $item['author'];
            $can_edit = false;
            $can_delete = false;
            $editconfig = extension::getInstance()->getConfig('edit_time', 'comments', 'modules', 'int');
            if ($userid > 0) {
                if (($poster_id == $userid && (time() - $item['time']) <= $editconfig) || permission::getInstance()->have('comment/edit')) {
                    $can_edit = true;
                }
                if (permission::getInstance()->have('comment/delete')) {
                    $can_delete = true;
                }
            }
            $params[] = array(
                'author_id' => $poster_id,
                'author_nick' => user::getInstance()->get('nick', $poster_id),
                'author_avatar' => user::getInstance()->buildAvatar('small', $poster_id),
                'comment_text' => extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml')->bbcode2html($item['comment']),
                'comment_date' => system::getInstance()->toDate($item['time'], 'h'),
                'unixtime' => $item['time'],
                'comment_id' => $item['id'],
                'can_edit' => $can_edit,
                'can_delete' => $can_delete,
                'guest_name' => system::getInstance()->nohtml($item['guest_name'])
            );
        }
        $stmt = null;
        return $params;
    }

    public function buildCommentTemplate($way = null, $end = 0, $show_all = false, $add_params = null) {
        $params = $this->getCommentsParams($way, $end, $show_all);
        return template::getInstance()->twigRender('modules/comments/comment_list.tpl', array('local' => $params, 'add' => $add_params));
    }
}