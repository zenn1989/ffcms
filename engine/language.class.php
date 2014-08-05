<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

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
        $lang = null;
        if(loader === 'front' && router::getInstance()->getPathLanguage() != null && self::canUse(router::getInstance()->getPathLanguage())) // did we have language in path for front iface?
            $lang = router::getInstance()->getPathLanguage();
        elseif((loader === 'api' || loader === 'install') && self::canUse($_COOKIE['ffcms_lang'])) // did language defined for API scripts?
            $lang = $_COOKIE['ffcms_lang'];
        elseif($_SERVER['HTTP_ACCEPT_LANGUAGE'] != null && self::canUse(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) && loader !== 'back') // did we have lang mark in browser?
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        else // no ? then use default language
            $lang = property::getInstance()->get('lang');
        self::$userLang = $lang;
        $file = root . '/language/' . $lang . '.ini';
        $addfile = root . '/language/' . $lang . '.custom.ini';
        self::getLanguageFile($file);
        self::getLanguageFile($addfile);
        // additional theme lang file
        $theme_langfile = root . '/' . property::getInstance()->get('tpl_dir') . '/' . property::getInstance()->get('tpl_name') . '/' . $lang . '.ini';
        self::getLanguageFile($theme_langfile);
    }

    protected static function getLanguageFile($file) {
        if(!file_exists($file))
            return;
        $lang_array = ini::getInstance()->read($file, true);
        foreach($lang_array[loader] as $key=>$value)
            template::getInstance()->set(template::TYPE_LANGUAGE, $key, $value);
        if(loader === 'api') { // api loader also use front language data
            foreach($lang_array['front'] as $key=>$value)
                template::getInstance()->set(template::TYPE_LANGUAGE, $key, $value);
        }
    }

    protected static function loadAvailable() {
        if(!file_exists(root . '/language/'))
            return;
        $scan = scandir(root . '/language/');
        $found_language = array();
        // get all available
        foreach($scan as $file) {
            if(!system::getInstance()->prefixEquals($file, '.') && system::getInstance()->suffixEquals($file, '.ini'))
                $found_language = system::getInstance()->arrayAdd(strstr($file, '.', true), $found_language);
        }
        // check if exists
        foreach($found_language as $check_language) {
            if(file_exists(root . '/language/' . $check_language . '.ini'))
                self::$available[] = $check_language;
        }
    }

    /**
     * Get current user language
     * @return string
     */
    public function getUseLanguage() {
        return self::$userLang;
    }

    /**
     * Get all available languages in system
     * @return array
     */
    public function getAvailable() {
        return self::$available;
    }

    /**
     * Get language value by key
     * @param string $lang
     * @return null|string
     */
    public function get($lang) {
        return template::getInstance()->get(template::TYPE_LANGUAGE, $lang);
    }

    /**
     * Check is $lang available to use on website
     * @param string $lang
     * @return bool
     */
    public static function canUse($lang) {
        return in_array($lang, self::$available);
    }

    /**
     * Write language data lines to ini file. Example of usage: language::getInstance()->add(
     * array('ru' => array('front' => array('lang_opt' => 'lang_data', 'lang_opt_2' => 'lang_data2')))
     * ) - will be write to ru.custom.ini data: [front]lang_opt = 'lang_data' \n 'lang_opt_2' => 'lang_data2'
     * @params array $data
     */
    public function add($data) {
        if(!is_array($data))
            return false;
        $total_result = true;
        foreach($data as $language=>$object) {
            $lang_defined_file = root . '/language/' . $language . '.custom.ini';
            $lang_defined = array();
            if(file_exists($lang_defined_file))
                $lang_defined = ini::getInstance()->read($lang_defined_file, true);
            $write_array = array_replace_recursive($lang_defined,$object);
            $res = ini::getInstance()->write($write_array, $lang_defined_file, true);
            if($res === false) // if not writed once - total write is failed, no reason to switch on true.
                $total_result = false;
        }
        return $total_result;
    }
}