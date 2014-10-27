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


class modules_news_top_discus_back extends \engine\singleton {

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.4';
    }

    public function make() {
        $params = array();

        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['config']['discus_count'] = extension::getInstance()->getConfig('discus_count', 'news_top_discus', extension::TYPE_MODULE, 'int');
        $params['config']['discus_days'] = extension::getInstance()->getConfig('discus_days', 'news_top_discus', extension::TYPE_MODULE, 'int');

        return template::getInstance()->twigRender('modules/news_top_discus/settings.tpl', $params);
    }

    public function _accessData() {
        return array(
            'admin/modules/news_top_discus',
        );
    }
}