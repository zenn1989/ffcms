<?php

use engine\system;
use engine\template;
use engine\admin;
use engine\extension;

class modules_tagcloud_back {
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

        $params['config']['tag_count'] = extension::getInstance()->getConfig('tag_count', 'tagcloud', extension::TYPE_MODULE, 'int');

        return template::getInstance()->twigRender('modules/tagcloud/settings.tpl', $params);
    }
}