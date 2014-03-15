<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\language;
use engine\property;

class api_changelanguage_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $to = system::getInstance()->get('to');
        $refer = $_SERVER['HTTP_REFERER'];
        if(language::getInstance()->canUse($to) && system::getInstance()->prefixEquals($refer, property::getInstance()->get('url'))) {
            $uri = system::getInstance()->altexplode('/', substr($refer, strlen(property::getInstance()->get('url'))));
            if(!property::getInstance()->get('user_friendly_url'))
                array_shift($uri);
            array_shift($uri);
            $uri_no_lang = system::getInstance()->altimplode('/', $uri);
            $uri_target = '/' . $to . '/';
            $uri_target .= $uri_no_lang;
            system::getInstance()->redirect($uri_target);
        }
    }
}