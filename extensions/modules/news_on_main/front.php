<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\extension;
use engine\database;
use engine\property;
use engine\system;
use engine\language;
use engine\user;
use engine\template;

class modules_news_on_main_front extends \engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $component_news = extension::getInstance()->call(extension::TYPE_COMPONENT, 'news');
        if(!is_object($component_news))
            return;
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $component_news->viewCategory(true));
    }
}