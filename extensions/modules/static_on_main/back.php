<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\template;
use engine\admin;
use engine\database;
use engine\property;
use engine\extension;
use engine\system;
use engine\language;

class modules_static_on_main_back {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $params = array();

        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['config']['show_date'] = extension::getInstance()->getConfig('show_date', 'static_on_main', extension::TYPE_MODULE, 'int');
        $params['config']['news_id'] = extension::getInstance()->getConfig('news_id', 'static_on_main', extension::TYPE_MODULE, 'int');

        $stmt = database::getInstance()->con()->prepare("SELECT `id`, `title` FROM ".property::getInstance()->get('db_prefix')."_com_static ORDER BY `id` DESC");
        $stmt->execute();
        $resultAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($resultAll as $row) {
            $serial_title = unserialize($row['title']);
            $params['staticpages'][] = array(
                'id' => $row['id'],
                'title' => $serial_title[language::getInstance()->getUseLanguage()]
            );
        }
        $stmt = null;

        return template::getInstance()->twigRender('modules/static_on_main/settings.tpl', $params);
    }

    public function accessData() {
        return array(
            'admin/modules/static_on_main',
        );
    }

}