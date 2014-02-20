<?php

use engine\system;
use engine\database;
use engine\property;
use engine\template;
use engine\admin;
use engine\user;
use engine\extension;

class modules_comments_back {
    protected static $instance = null;

    const ITEM_PER_PAGE = 10;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
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
        }
        return $content;
    }

    private function viewCommentDelete() {
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $comment_id = (int)system::getInstance()->get('id');

        if(system::getInstance()->post('delete_comment') && $comment_id > 0) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=modules&action=comments");
        }

        $stmt = database::getInstance()->con()->prepare("SELECT comment FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $stmt = null;
            $params['comments']['data'] = array(
                'id' => $comment_id,
                'user_name' => user::getInstance()->get('nick', $result['author']),
                'text' => extension::getInstance(true)->call(extension::TYPE_HOOK, 'bbtohtml')->nobbcode($result['comment'])
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
        $params = array();

        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }

        $params['config']['comments_count'] = extension::getInstance()->getConfig('comments_count', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['time_delay'] = extension::getInstance()->getConfig('time_delay', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['edit_time'] = extension::getInstance()->getConfig('edit_time', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['min_length'] = extension::getInstance()->getConfig('min_length', 'comments', extension::TYPE_MODULE, 'int');
        $params['config']['max_length'] = extension::getInstance()->getConfig('max_length', 'comments', extension::TYPE_MODULE, 'int');

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        return template::getInstance()->twigRender('modules/comments/settings.tpl', $params);
    }

    private function viewCommentList() {
        $params = array();

        if(system::getInstance()->post('deleteSelected')) {
            $toDelete = system::getInstance()->post('check_array');
            if(is_array($toDelete) && sizeof($toDelete) > 0) {
                $listDelete = system::getInstance()->altimplode(',', $toDelete);
                if(system::getInstance()->isIntList($listDelete)) {
                    database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE id IN (".$listDelete.")");
                }
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $index = (int)system::getInstance()->get('index');
        $db_index = $index * self::ITEM_PER_PAGE;

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_mod_comments ORDER BY id DESC LIMIT ?,".self::ITEM_PER_PAGE);
        $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
        $stmt->execute();

        $resultFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        user::getInstance()->listload(system::getInstance()->extractFromMultyArray('author', $resultFetch));

        foreach($resultFetch as $row) {
            $params['comments']['list'][] = array(
                'id' => $row['id'],
                'user_id' => $row['author'],
                'user_name' => user::getInstance()->get('nick', $row['author']),
                'comment' => extension::getInstance(true)->call(extension::TYPE_HOOK, 'bbtohtml')->nobbcode($row['comment'])
            );
        }

        $params['pagination'] = template::getInstance()->showFastPagination($index, self::ITEM_PER_PAGE, $this->getTotalCommentCount(), '?object=modules&action=comments&index=');

        return template::getInstance()->twigRender('modules/comments/list.tpl', $params);

    }

    public function getTotalCommentCount() {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_comments");
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt = null;
        return $result[0];
    }
}