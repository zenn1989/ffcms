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

    protected $metadata = array();

    /**
     * Default data for main page.
     */
    public function init() {
        // title is globally also for 404 pages.
        $serial_title = property::getInstance()->get('seo_title');
        $serial_desc = property::getInstance()->get('seo_description');
        $serial_keywords = property::getInstance()->get('seo_keywords');
        $this->metadata['title'][] = $serial_title[language::getInstance()->getUseLanguage()];
        if(router::getInstance()->isMain()) {
            $this->metadata['description'][] = $serial_desc[language::getInstance()->getUseLanguage()];
            $this->metadata['keywords'][] = $serial_keywords[language::getInstance()->getUseLanguage()];
        }
        $this->metadata['global_title'] = $serial_title[language::getInstance()->getUseLanguage()];
    }

    /**
     * Adding to meta tag data values
     * @param string('title', 'description', 'keywords') $tag
     * @param string $data
     */
    public function add($tag, $data) {
        if(in_array($tag, array('title', 'description', 'keywords'))) {
            if(system::getInstance()->length($data) > 0)
                $this->metadata[$tag][] = $data;
        }
    }

    public function compile() {
        template::getInstance()->set(template::TYPE_META, 'description', system::getInstance()->altimplode('. ', $this->metadata['description']));
        template::getInstance()->set(template::TYPE_META, 'keywords', system::getInstance()->altimplode('. ', $this->metadata['keywords']));
        template::getInstance()->set(template::TYPE_META, 'global_title', $this->metadata['global_title']);
        if(property::getInstance()->get('multi_title'))
            template::getInstance()->set(template::TYPE_META, 'title', system::getInstance()->altimplode(" - ", array_reverse($this->metadata['title'])));
        else
            template::getInstance()->set(template::TYPE_META, 'title', array_pop($this->metadata['title']));
        template::getInstance()->set(template::TYPE_META, 'generator', 'FFCMS engine: ffcms.ru. Version: ' . version);
    }


}