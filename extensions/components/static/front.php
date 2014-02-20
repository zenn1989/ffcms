<?php

use engine\property;
use engine\database;
use engine\language;
use engine\meta;
use engine\system;
use engine\template;
use engine\router;

class components_static_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $way = router::getInstance()->shiftUriArray();
        $path = system::getInstance()->altimplode('/', $way);
        $content = $this->display($path);
        if($content != null) {
            template::getInstance()->set(template::TYPE_CONTENT, 'body', $this->display($path));
        }
    }

    /**
     * Set in body position static page for $pathway
     * @param $pathway
     */
    public function display($pathway, $id = null, $show_date = true) {
        $stmt = null;
        if(is_null($id)) {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE pathway = ?");
            $stmt->bindParam(1, $pathway, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE id = ?");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
        }
        if($stmt != null && $result = $stmt->fetch()) {
            $serial_title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $serial_keywords = unserialize($result['keywords']);
            $serial_description = unserialize($result['description']);
            meta::getInstance()->add('title', $serial_title[language::getInstance()->getUseLanguage()]);
            meta::getInstance()->add('keywords', $serial_keywords[language::getInstance()->getUseLanguage()]);
            meta::getInstance()->add('description', $serial_description[language::getInstance()->getUseLanguage()]);
            $params = array(
                'title' => $serial_title[language::getInstance()->getUseLanguage()],
                'text' => $serial_text[language::getInstance()->getUseLanguage()],
                'date' => system::getInstance()->toDate($result['date'], 'd'),
                'show_date' => $show_date
            );
            return template::getInstance()->twigRender('components/static/page.tpl', array('local' => $params));
        }
        return null;
    }
}