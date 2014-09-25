<?php

use engine\system;
use engine\database;
use engine\property;
use engine\csrf;
use engine\admin;
use engine\template;
use engine\user;
use engine\permission;
use engine\extension;

class components_stream_back extends engine\singleton {
    protected static $instance = null;
    const ITEM_PER_PAGE = 10;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.2';
    }

    public function accessData() {
        return array(
            'admin/components/stream',
            'admin/components/stream/list',
            'admin/components/stream/settings',
            'admin/components/stream/delete'
        );
    }

    public function make() {
        $make = system::getInstance()->get('make');
        $content = null;
        switch($make) {
            case 'list':
            case null:
                $content = $this->viewStreamList();
                break;
            case 'settings':
                $content = $this->viewStreamSettings();
                break;
        }
        return $content;
    }

    private function viewStreamList() {
        csrf::getInstance()->buildToken();
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $page_index = (int)system::getInstance()->get('index');
        $db_index = $page_index * self::ITEM_PER_PAGE;

        if(system::getInstance()->post('deleteSelected') && csrf::getInstance()->check()) {
            if(permission::getInstance()->have('global/owner') || permission::getInstance()->have('admin/components/stream/delete')) {
                $toDelete = system::getInstance()->post('check_array');
                if(is_array($toDelete) && sizeof($toDelete) > 0) {
                    $listDelete = system::getInstance()->altimplode(',', $toDelete);
                    if(system::getInstance()->isIntList($listDelete)) {
                        database::getInstance()->con()->query("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_stream WHERE id IN (".$listDelete.")");
                    }
                }
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_stream ORDER BY `date` DESC LIMIT ?,".self::ITEM_PER_PAGE);
        $stmt->bindParam(1, $db_index, \PDO::PARAM_INT);
        $stmt->execute();

        $resultAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        $ids = system::getInstance()->extractFromMultyArray('caster_id', $resultAll);
        user::getInstance()->listload($ids);

        foreach($resultAll as $row) {
            $params['stream'][] = array(
                'id' => $row['id'],
                'type' => $row['type'],
                'user_id' => $row['caster_id'],
                'user_name' => user::getInstance()->get('nick', $row['caster_id']),
                'url' => $row['target_object'],
                'text' => system::getInstance()->nohtml($row['text_preview']),
                'date' => system::getInstance()->todate($row['date'], 'h')
            );
        }

        $count_all = extension::getInstance()->call(extension::TYPE_COMPONENT, 'stream', false)->streamCount();
        $params['pagination'] = template::getInstance()->showFastPagination($page_index, self::ITEM_PER_PAGE, $count_all, '?object=components&action=stream&index=');

        return template::getInstance()->twigRender('components/stream/list.tpl', $params);
    }

    private function viewStreamSettings() {
        csrf::getInstance()->buildToken();
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs() && csrf::getInstance()->check()) {
                $params['notify']['save_success'] = true;
            }
        }

        $params['config']['count_stream_page'] = extension::getInstance()->getConfig('count_stream_page', 'stream', extension::TYPE_COMPONENT, 'int');

        return template::getInstance()->twigRender('components/stream/settings.tpl', $params);
    }
}