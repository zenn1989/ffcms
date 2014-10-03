<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\database;
use engine\property;
use engine\template;
use engine\admin;
use engine\user;
use engine\extension;
use engine\csrf;

class modules_comments_back {
    protected static $instance = null;

    const ITEM_PER_PAGE = 10;

    const FILTER_ALL = 0;
    const FILTER_MODERATE = 1;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
            case 'list':
                $content = $this->viewCommentList();
                break;
            case 'edit':
                $content = $this->viewCommentEdit();
                break;
            case 'settings':
                $content = $this->viewCommentSettings();
                break;
            case 'delete':
                $content = $this->viewCommentDelete();
                break;
            case 'aprove':
                $this->viewCommentAprove();
                break;
            case 'hide':
                $this->viewCommentHide();
                break;
        }
        return $content;
    }

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.3';
    }

    public function accessData() {
        return array(
            'admin/modules/comments',
            'admin/modules/comments/list',
            'admin/modules/comments/edit',
            'admin/modules/comments/settings',
            'admin/modules/comments/delete',
            'admin/modules/comments/aprove',
            'admin/modules/comments/hide',
        );
    }

    private function viewCommentHide() {
        $comment_id = (int)system::getInstance()->get('id');

        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_mod_comments SET moderate = 1 WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;

        system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=modules&action=comments");
    }

    private function viewCommentAprove() {
        $comment_id = (int)system::getInstance()->get('id');

        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_mod_comments SET moderate = 0 WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;

        system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=modules&action=comments");
    }

    private function viewCommentDelete() {
        csrf::getInstance()->buildToken();
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $comment_id = (int)system::getInstance()->get('id');

        if(system::getInstance()->post('delete_comment') && $comment_id > 0 && csrf::getInstance()->check()) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=modules&action=comments");
        }

        $stmt = database::getInstance()->con()->prepare("SELECT comment,author,guest_name FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $stmt = null;
            $params['comments']['data'] = array(
                'id' => $comment_id,
                'user_name' => $result['author'] > 0 ? user::getInstance()->get('nick', $result['author']) : $result['guest_name'],
                'text' => extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml')->nobbcode($result['comment'])
            );
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=modules&action=comments');
        }

        return template::getInstance()->twigRender('modules/comments/delete.tpl', $params);
    }

    private function viewCommentEdit() {
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $comment_id = (int)system::getInstance()->get('id');

        if (system::getInstance()->post('save_comment') && $comment_id > 0 && strlen(system::getInstance()->post('comment_text')) > 0) {
            $new_comment_text = system::getInstance()->nohtml(system::getInstance()->post('comment_text'));
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_mod_comments SET comment = ? WHERE id = ?");
            $stmt->bindParam(1, $new_comment_text, PDO::PARAM_STR);
            $stmt->bindParam(2, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
            $params['notify']['comment_saved'] = true;
        }

        $stmt = database::getInstance()->con()->prepare("SELECT comment FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $stmt = null;
            $params['comments']['text'] = $result['comment'];
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . '?object=modules&action=comments');
        }

        return template::getInstance()->twigRender('modules/comments/edit.tpl', $params);
    }

    private function viewCommentSettings() {
        csrf::getInstance()->buildToken();
        $params = array();

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }

        $params['config']['comments_count'] = extension::getInstance()->getConfig('comments_count', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['time_delay'] = extension::getInstance()->getConfig('time_delay', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['edit_time'] = extension::getInstance()->getConfig('edit_time', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['min_length'] = extension::getInstance()->getConfig('min_length', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['max_length'] = extension::getInstance()->getConfig('max_length', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['guest_comment'] = extension::getInstance()->getConfig('guest_comment', 'comments', extension::TYPE_MODULE, 'int');

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        return template::getInstance()->twigRender('modules/comments/settings.tpl', $params);
    }

    private function viewCommentList() {
        csrf::getInstance()->buildToken();
        $params = array();

        if(system::getInstance()->post('deleteSelected') && csrf::getInstance()->check()) {
            $toDelete = system::getInstance()->post('check_array');
            if(is_array($toDelete) && sizeof($toDelete) > 0) {
                $listDelete = system::getInstance()->altimplode(',', $toDelete);
                if(system::getInstance()->isIntList($listDelete)) {
                    database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id IN (".$listDelete.")");
                }
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $filter = (int)system::getInstance()->get('filter');
        $index = (int)system::getInstance()->get('index');
        $db_index = $index * self::ITEM_PER_PAGE;

        $stmt = null;

        if($filter == self::FILTER_MODERATE) {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE moderate = 1 ORDER BY id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments ORDER BY id DESC LIMIT ?,".self::ITEM_PER_PAGE);
            $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
            $stmt->execute();
        }

        $resultFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $authors_ids = system::getInstance()->extractFromMultyArray('author', $resultFetch);
        if(sizeof($authors_ids) > 1) // 2 or more
            user::getInstance()->listload(system::getInstance()->extractFromMultyArray('author', $resultFetch));

        foreach($resultFetch as $row) {
            $params['comments']['list'][] = array(
                'id' => $row['id'],
                'user_id' => $row['author'],
                'user_name' => user::getInstance()->get('nick', $row['author']),
                'comment' => extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml')->nobbcode($row['comment']),
                'guest_name' => system::getInstance()->nohtml($row['guest_name']),
                'moderate' => $row['moderate'],
                'date' => system::getInstance()->toDate($row['time'], 'h'),
                'uri' => $row['pathway']
            );
        }

        $params['pagination'] = template::getInstance()->showFastPagination($index, self::ITEM_PER_PAGE, $this->getTotalCommentCount($filter), '?object=modules&action=comments&filter='.$filter.'&index=');

        return template::getInstance()->twigRender('modules/comments/list.tpl', $params);

    }

    public function getTotalCommentCount($filter) {
        $stmt = null;
        if($filter == self::FILTER_MODERATE)
            $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE moderate = 1");
        else
            $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_comments");
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }
}