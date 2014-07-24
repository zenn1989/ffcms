<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\template;
use engine\admin;
use engine\database;
use engine\property;

class components_feedback_back {
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
            case 'list':
                $content = $this->viewFeedList();
                break;
            case 'read':
                $content = $this->viewFeedRead();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    public function accessData() {
        return array(
            'admin/components/feedback',
            'admin/components/feedback/list',
            'admin/components/feedback/read',
        );
    }

    private function viewFeedRead() {
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $feed_id = (int)system::getInstance()->get('id');

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_feedback WHERE id = ?");
        $stmt->bindParam(1, $feed_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result['title'] = system::getInstance()->htmlQuoteDecode($result['title']);
            $result['text'] = system::getInstance()->htmlQuoteDecode($result['text']);
            $params['feedback']['result'] = $result;
        } else {
            system::getInstance()->redirect($_SERVER['PHP_SELF'] . "?object=components&action=user");
        }

        return template::getInstance()->twigRender('components/feedback/read.tpl', $params);
    }

    private function viewFeedList() {
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $index = (int)system::getInstance()->get('index');
        $db_index = $index * self::ITEM_PER_PAGE;
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_feedback ORDER BY `id` DESC LIMIT ?,".self::ITEM_PER_PAGE);
        $stmt->bindParam(1, $db_index, PDO::PARAM_INT);
        $stmt->execute();

        $params['feedback']['result'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $params['pagination'] = template::getInstance()->showFastPagination($index, self::ITEM_PER_PAGE, $this->getFeedCount(), '?object=components&action=feedback&index=');

        return template::getInstance()->twigRender('components/feedback/list.tpl', $params);
    }

    private function getFeedCount()
    {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_feedback");
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        return $res[0];
    }
}