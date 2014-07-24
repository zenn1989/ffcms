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
use engine\extension;

class modules_lastcomments_back {
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

        $params['config']['last_count'] = extension::getInstance()->getConfig('last_count', 'lastcomments', extension::TYPE_MODULE, 'int');
        $params['config']['text_length'] = extension::getInstance()->getConfig('text_length', 'lastcomments', extension::TYPE_MODULE, 'int');

        return template::getInstance()->twigRender('modules/lastcomments/settings.tpl', $params);
    }

    public function accessData() {
        return array(
            'admin/modules/lastcomments',
        );
    }
}