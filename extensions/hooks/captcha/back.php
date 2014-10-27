<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\admin;
use engine\extension;
use engine\template;
use engine\system;
use engine\csrf;

class hooks_captcha_back extends \engine\singleton {

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.4';
    }

    public function make() {
        csrf::getInstance()->buildToken();
        $params = array();

        if(system::getInstance()->post('submit') && csrf::getInstance()->check()) {
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

    public function accessData() {
        return array(
            'admin/hooks/captcha'
        );
    }


}



?>