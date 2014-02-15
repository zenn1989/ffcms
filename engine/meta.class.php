<?php

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
        self::$metadata['title'][] = property::getInstance()->get('seo_title');
        if(router::getInstance()->isMain()) {
            self::$metadata['description'][] = property::getInstance()->get('seo_description');
            self::$metadata['keywords'][] = property::getInstance()->get('seo_keywords');
        }
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
        if(property::getInstance()->get('multi_title'))
            template::getInstance()->set(template::TYPE_META, 'title', system::getInstance()->altimplode(" - ", array_reverse(self::$metadata['title'])));
        else
            template::getInstance()->set(template::TYPE_META, 'title', array_pop(self::$metadata['title']));
        template::getInstance()->set(template::TYPE_META, 'generator', 'FFCMS engine: ffcms.ru. Version: ' . version);
    }


}