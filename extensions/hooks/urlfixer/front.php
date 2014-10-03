<?php

use engine\property;
use engine\system;
use engine\language;
use engine\extension;

class hooks_urlfixer_front extends \engine\singleton {
    protected static $instance = null;

    public function _version() {
        return '1.0.1';
    }

    public function _compatable() {
        return '2.0.2';
    }

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {}

    /**
     * Search and replace URL's for site mirrors
     * @param $text
     * @param bool $special_syntax
     * @return array|mixed
     */
    public function fix($text, $special_syntax = true) {
        if(!system::getInstance()->contains(';', property::getInstance()->get('source_url'))) // if only single url is defined
            return $text;
        if(is_array($text)) {
            $result = array();
            foreach($text as $language=>$i_text) {
                $result[$language] = $this->fix($i_text);
            }
            return $result;
        } else {
            if(!$special_syntax) {
                $text = system::getInstance()->nohtml($text);
                $bbobject = extension::getInstance()->call(extension::TYPE_HOOK, 'bbtohtml');
                if(is_object($bbobject))
                    $text = $bbobject->nobbcode($text);
            }
            $available_url = system::getInstance()->altexplode(';', property::getInstance()->get('source_url'));
            $used_url = property::getInstance()->get('script_url');

            $result = str_replace($available_url, $used_url, $text);

            if(property::getInstance()->get('user_friendly_url')) {
                // if url/index.php/ is used
                // url/index.php/ to url/
                $no_humanurls = $used_url . '/index.php/';
                $result = str_replace($no_humanurls, $used_url . '/', $result);
            } else {
                // try to set links to non-user friendly model /index.php/lang/ from /lang/
                foreach(language::getInstance()->getAvailable() as $s_lang) {
                    $source_human_urls = $used_url . '/' . $s_lang . '/';
                    $replacement_human_urls = $used_url . '/index.php/' . $s_lang . '/';

                    $result = str_replace($source_human_urls, $replacement_human_urls, $result);
                }
            }
            // if disabled multi-lang
            // /ru/page.html to /page.html, /index.php/ru/page.html to /index.php/page.html
            if(!property::getInstance()->get('use_multi_language')) {
                $to_replace = array();
                $replacement = null;
                foreach(language::getInstance()->getAvailable() as $s_lang) {
                    $to_replace[] = $used_url . '/index.php/' . $s_lang . '/';
                    $to_replace[] = $used_url . '/' . $s_lang . '/';
                }
                if(property::getInstance()->get('user_friendly_url')) {
                    $replacement = $used_url . '/';
                } else {
                    $replacement = $used_url . '/index.php/';
                }
                $result = str_replace($to_replace, $replacement, $result);
            }

            return $result;
        }
    }
}