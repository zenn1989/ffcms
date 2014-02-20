<?php

namespace engine;

class language extends singleton {
    protected static $instance = null;
    protected static $available = array();
    protected static $userLang = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::loadAvailable();
            self::loadLanguage();
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected static function loadLanguage() {
        $lang = property::getInstance()->get('lang');
        if($_COOKIE['ffcms_lang'] != null && self::canUse($_COOKIE['ffcms_lang']))
            $lang = $_COOKIE['ffcms_lang'];
        elseif($_SERVER['HTTP_ACCEPT_LANGUAGE'] != null && self::canUse(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)))
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        self::$userLang = $lang;
        $file = null;
        $addfile = null;
        // default language files
        if(loader == 'back') {
            $file = root . '/language/' . $lang . '.back.lang';
            $addfile = root . '/language/' . $lang . '.back.addition.lang';
        } elseif(loader == 'install') {
            $file = root . '/language/' . $lang . '.install.lang';
        } else {
            $file = root . '/language/' . $lang . '.front.lang';
            $addfile = root . '/language/' . $lang . '.front.addition.lang';
        }
        self::getLanguageFile($file);
        self::getLanguageFile($addfile);
        // additional theme lang file
        if(loader != 'back' && loader != 'front') {
            $theme_langfile = root . '/' . property::getInstance()->get('tpl_dir') . '/' . property::getInstance()->get('tpl_name') . '/' . $lang . '.lang';
            self::getLanguageFile($theme_langfile);
        }
    }

    protected static function getLanguageFile($file) {
        if(!file_exists($file))
            return;
        $content = @file_get_contents($file);
        $lang_array = explode("\n", $content);
        foreach($lang_array as $lang_line) {
            if(strlen($lang_line) > 3 && !system::getInstance()->prefixEquals($lang_line, '#')) {
                list($tag, $value) = explode("<=>", $lang_line);
                $value = str_replace(array("\r\n","\r"), '', $value); // js issue with illegal
                template::getInstance()->set(template::TYPE_LANGUAGE, $tag, $value);
            }
        }
    }

    protected static function loadAvailable() {
        if(!file_exists(root . '/language/'))
            return;
        $scan = scandir(root . '/language/');
        $found_language = array();
        // get all available
        foreach($scan as $file) {
            if(!system::getInstance()->prefixEquals($file, '.') && system::getInstance()->suffixEquals($file, '.lang'))
                $found_language = system::getInstance()->arrayAdd(strstr($file, '.', true), $found_language);
        }
        // check if exists all files
        foreach($found_language as $check_language) {
            if(file_exists(root . '/language/' . $check_language . '.front.lang') && file_exists(root . '/language/' . $check_language . '.back.lang')
               && file_exists(root . '/language/' . $check_language . '.install.lang'))
                self::$available[] = $check_language;
        }
    }

    public function getUseLanguage() {
        return self::$userLang;
    }

    public function getAvailable() {
        return self::$available;
    }

    public function get($lang) {
        return template::getInstance()->get(template::TYPE_LANGUAGE, $lang);
    }

    public static function canUse($lang) {
        return in_array($lang, self::$available);
    }
}