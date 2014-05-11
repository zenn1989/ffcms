<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class meta extends singleton {
    protected static $instance = null;
    protected static $metadata = array();

    /**
     * @return meta
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::initMain();
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Default data for main page.
     */
    protected static function initMain() {
        // title is globally also for 404 pages.
        $serial_title = property::getInstance()->get('seo_title');
        $serial_desc = property::getInstance()->get('seo_description');
        $serial_keywords = property::getInstance()->get('seo_keywords');
        self::$metadata['title'][] = $serial_title[language::getInstance()->getUseLanguage()];
        if(router::getInstance()->isMain()) {
            self::$metadata['description'][] = $serial_desc[language::getInstance()->getUseLanguage()];
            self::$metadata['keywords'][] = $serial_keywords[language::getInstance()->getUseLanguage()];
        }
        self::$metadata['global_title'] = $serial_title[language::getInstance()->getUseLanguage()];
    }

    /**
     * Adding meta data values
     * @param string('title', 'description', 'keywords') $tag
     * @param string $data
     */
    public function add($tag, $data) {
        if(in_array($tag, array('title', 'description', 'keywords')))
            self::$metadata[$tag][] = $data;
    }

    public function compile() {
        template::getInstance()->set(template::TYPE_META, 'description', system::getInstance()->altimplode('. ', self::$metadata['description']));
        template::getInstance()->set(template::TYPE_META, 'keywords', system::getInstance()->altimplode('. ', self::$metadata['keywords']));
        template::getInstance()->set(template::TYPE_META, 'global_title', self::$metadata['global_title']);
        if(property::getInstance()->get('multi_title'))
            template::getInstance()->set(template::TYPE_META, 'title', system::getInstance()->altimplode(" - ", array_reverse(self::$metadata['title'])));
        else
            template::getInstance()->set(template::TYPE_META, 'title', array_pop(self::$metadata['title']));
        template::getInstance()->set(template::TYPE_META, 'generator', 'FFCMS engine: ffcms.ru. Version: ' . version);
    }


}