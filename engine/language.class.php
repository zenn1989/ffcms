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

    protected $available = array();
    protected $userLang = null;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function getLanguageFile($file) {
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

    public function init() {
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
                $this->available[] = $check_language;
        }
    }

    /**
     * Get current user language
     * @return string
     */
    public function getUseLanguage() {
        return $this->userLang;
    }

    /**
     * Set use langauge for this session. As default this function was called from router on build process.
     * @param string $language
     */
    public function setUseLanguage($language) {
        $file = root . '/language/' . $language . '.ini';
        $addfile = root . '/language/' . $language . '.custom.ini';
        $this->getLanguageFile($file);
        $this->getLanguageFile($addfile);
        // additional theme lang file
        $theme_langfile = root . '/' . property::getInstance()->get('tpl_dir') . '/' . property::getInstance()->get('tpl_name') . '/' . $language . '.ini';
        $this->getLanguageFile($theme_langfile);
        $this->userLang = $language;
    }

    /**
     * Get all available languages in system
     * @return array
     */
    public function getAvailable() {
        return $this->available;
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
    public function canUse($lang) {
        return in_array($lang, $this->available);
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