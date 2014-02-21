<?php

use engine\admin;
use engine\extension;
use engine\template;
use engine\system;

class hooks_captcha_back {
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

        $params['config']['captcha_type'] = extension::getInstance()->getConfig('captcha_type', 'captcha', extension::TYPE_HOOK, 'str');
        $params['config']['captcha_publickey'] = extension::getInstance()->getConfig('captcha_publickey', 'captcha', extension::TYPE_HOOK, 'str');
        $params['config']['captcha_privatekey'] = extension::getInstance()->getConfig('captcha_privatekey', 'captcha', extension::TYPE_HOOK, 'str');

        return template::getInstance()->twigRender('hooks/captcha/settings.tpl', $params);
    }


}



?>