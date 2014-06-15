<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\property;
use engine\database;
use engine\system;
use engine\language;
use engine\router;
use engine\meta;
use engine\template;

class components_search_front {

    protected static $instance = null;
    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $params = array();
        meta::getInstance()->add('title', language::getInstance()->get('search_seo_title'));
        $way = router::getInstance()->shiftUriArray();
        $query = system::getInstance()->nohtml($way[0]);
        $params['query'] = null;
        if(system::getInstance()->length($query) > 2 && !system::getInstance()->prefixEquals($query, "?")) {
            $params['search'] = array_merge($this->searchOnNews($query), $this->searchOnPage($query));
            $params['query'] = $query;
        }
        $render = template::getInstance()->twigRender(
            'components/search/search.tpl',
            array('local' => $params)
        );
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $render);
    }

    private function searchOnNews($query)
    {
        $params = array();
        $queryBuild = '%'.$query.'%';
        $stmt = database::getInstance()->con()->prepare(
        "SELECT a.title,a.text,a.link,a.category,a.date,b.category_id,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a,
        ".property::getInstance()->get('db_prefix')."_com_news_category b
        WHERE a.category = b.category_id AND (a.text like ? OR a.title like ?) ORDER BY a.`date` DESC LIMIT 50"
        );
        $stmt->bindParam(1, $queryBuild, PDO::PARAM_STR);
        $stmt->bindParam(2, $queryBuild, PDO::PARAM_STR);
        $stmt->execute();
        $compiled_body = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $text = system::getInstance()->altsubstr(system::getInstance()->nohtml($serial_text[language::getInstance()->getUseLanguage()]), 0, 200);
            $link = "news/";
            if($result['path'] != null) {
                $link .= $result['path']."/";
            }
            $link .= $result['link'];
            $params['news'][] = array(
                'link' => $link,
                'title' => $title[language::getInstance()->getUseLanguage()],
                'snippet' => $text,
                'date' => system::getInstance()->toDate($result['date'], 'h')
            );
        }
        $stmt = null;
        return $params;
    }

    private function searchOnPage($query)
    {
        $params = array();
        $queryBuild = '%'.$query.'%';
        $stmt = database::getInstance()->con()->prepare("SELECT title,text,pathway,date FROM ".property::getInstance()->get('db_prefix')."_com_static WHERE text like ? OR title like ? ORDER BY `date` LIMIT 50");
        $stmt->bindParam(1, $queryBuild, PDO::PARAM_STR);
        $stmt->bindParam(2, $queryBuild, PDO::PARAM_STR);
        $stmt->execute();
        $compiled_body = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $text = system::getInstance()->altsubstr(system::getInstance()->nohtml($serial_text[language::getInstance()->getUseLanguage()]), 0, 200);
            $link = "static/".$result['pathway'];
            $params['static'][] = array(
                'link' => $link,
                'title' => $title[language::getInstance()->getUseLanguage()],
                'snippet' => $text,
                'date' => system::getInstance()->toDate($result['date'], 'h')
            );
        }
        return $params;
    }
}