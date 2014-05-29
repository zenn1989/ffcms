<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\admin;
use engine\extension;
use engine\template;


class modules_news_top_view_back {
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

        $params['config']['viewtop_count'] = extension::getInstance()->getConfig('viewtop_count', 'news_top_view', extension::TYPE_MODULE, 'int');
        $params['config']['viewtop_days'] = extension::getInstance()->getConfig('viewtop_days', 'news_top_view', extension::TYPE_MODULE, 'int');

        return template::getInstance()->twigRender('modules/news_top_view/settings.tpl', $params);
    }
}