<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

if (!extension::registerPathWay('search', 'search')) {
    exit("Component search cannot be registered!");
}

class com_search_front implements com_front
{
    public function load()
    {
        global $engine;
        $engine->meta->add('title', $engine->language->get('search_seo_title'));
        $theme_head = $engine->template->get('search_head', 'components/search/');
        $way = $engine->page->getPathway();
        $query = $engine->system->nohtml(urldecode($way[1]));
        $query = str_replace(array('"', "'"), "", $query);
        if($engine->system->length($query) > 3) {
            $engine->rule->add('com.search.query_make', true);
        }
        $newsSearch = $this->searchOnNews($query);
        $pageSearch = $this->searchOnPage($query);
        $engine->page->setContentPosition('body', $engine->template->assign(array('search_query', 'search_news', 'search_pages'), array($query, $newsSearch, $pageSearch), $theme_head));
    }

    private function searchOnNews($query)
    {
        global $engine;
        $theme_body = $engine->template->get('search_body', 'components/search/');
        $queryBuild = '%'.$query.'%';
        $stmt = $engine->database->con()->prepare("SELECT a.title,a.text,a.link,a.category,b.category_id,b.path FROM {$engine->constant->db['prefix']}_com_news_entery a, {$engine->constant->db['prefix']}_com_news_category b WHERE a.category = b.category_id AND (a.text like ? OR a.title like ?) LIMIT 50");
        $stmt->bindParam(1, $queryBuild, PDO::PARAM_STR);
        $stmt->bindParam(2, $queryBuild, PDO::PARAM_STR);
        $stmt->execute();
        $compiled_body = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $text = $engine->framework->set($serial_text[$engine->language->getCustom()])->nohtml()->altsubstr(0, 200)->get();
            $link = "news/";
            if($result['path'] != null) {
                $link .= $result['path']."/";
            }
            $link .= $result['link'];
            $compiled_body .= $engine->template->assign(array('search_link', 'search_title', 'search_snippet'), array($link, $title[$engine->language->getCustom()], $text), $theme_body);
        }
        if($compiled_body == null) {
            $compiled_body = $engine->template->stringNotify('error', $engine->language->get('search_nothing_found'));
        }
        return $compiled_body;
    }

    private function searchOnPage($query)
    {
        global $engine;
        $theme_body = $engine->template->get('search_body', 'components/search/');
        $queryBuild = '%'.$query.'%';
        $stmt = $engine->database->con()->prepare("SELECT title,text,pathway FROM {$engine->constant->db['prefix']}_com_static WHERE text like ? OR title like ? LIMIT 50");
        $stmt->bindParam(1, $queryBuild, PDO::PARAM_STR);
        $stmt->bindParam(2, $queryBuild, PDO::PARAM_STR);
        $stmt->execute();
        $compiled_body = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $text = $engine->framework->set($serial_text[$engine->language->getCustom()])->nohtml()->altsubstr(0, 200)->get();
            $link = "static/".$result['pathway'];
            $compiled_body .= $engine->template->assign(array('search_link', 'search_title', 'search_snippet'), array($link, $title[$engine->language->getCustom()], $text), $theme_body);
        }
        if($compiled_body == null) {
            $compiled_body = $engine->template->stringNotify('error', $engine->language->get('search_nothing_found'));
        }
        return $compiled_body;

    }
}


?>