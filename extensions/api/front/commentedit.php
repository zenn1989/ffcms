<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\user;
use engine\database;
use engine\property;
use engine\extension;
use engine\template;
use engine\system;
use engine\permission;

class api_commentedit_front extends \engine\singleton {

    public function make() {
        $comment_id = (int)system::getInstance()->get('id');
        if($this->canEdit($comment_id)) {
            $params = array();
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $content = null;
            if ($result = $stmt->fetch()) {
                $params['comment'] = array(
                    'id' => $comment_id,
                    'body' => $result['comment']
                );
                echo template::getInstance()->twigRender('modules/comments/comment_api.tpl', array('local' => $params));
            }
            $stmt = null;
        }
    }

    public function canEdit($comment_id) {
        if(permission::getInstance()->have('global/owner')) // no limits for full admin
            return true;
        if(user::getInstance()->get('id') < 1)
            return false;
        if(!permission::getInstance()->have('global/write'))
            return false;
        $userid = user::getInstance()->get('id');
        $stmt = database::getInstance()->con()->prepare("SELECT author,time FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        if ($result = $stmt->fetch()) {
            $editconfig = extension::getInstance()->getConfig('edit_time', 'comments', 'modules', 'int');
            if ($result['author'] != $userid || (time() - $result['time']) > $editconfig && !permission::getInstance()->have('comment/edit')) {
                return false;
            }
        }
        return true;
    }
}