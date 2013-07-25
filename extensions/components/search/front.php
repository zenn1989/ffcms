<?php

if (!extension::registerPathWay('search', 'search')) {
    exit("Component search cannot be registered!");
}

class com_search_front implements com_front
{
    public function load()
    {
        global $page, $system, $template, $rule, $meta, $language;
        $meta->add('title', $language->get('search_seo_title'));
        $theme_head = $template->get('search_head', 'components/search/');
        $way = $page->getPathway();
        $query = $system->nohtml(urldecode($way[1]));
        if($system->length($query) > 3) {
            $rule->add('com.search.query_make', true);
        }
        $newsSearch = $this->searchOnNews($query);
        $pageSearch = $this->searchOnPage($query);
        $page->setContentPosition('body', $template->assign(array('search_query', 'search_news', 'search_pages'), array($query, $newsSearch, $pageSearch), $theme_head));
    }

    private function searchOnNews($query)
    {
        global $database, $constant, $language, $template, $framework;
        $theme_body = $template->get('search_body', 'components/search/');
        $queryBuild = '%'.$query.'%';
        $stmt = $database->con()->prepare("SELECT a.title,a.text,a.link,a.category,b.category_id,b.path FROM {$constant->db['prefix']}_com_news_entery a, {$constant->db['prefix']}_com_news_category b WHERE a.text like ? OR a.title like ? LIMIT 50");
        $stmt->bindParam(1, $queryBuild, PDO::PARAM_STR);
        $stmt->bindParam(2, $queryBuild, PDO::PARAM_STR);
        $stmt->execute();
        $compiled_body = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $text = $framework->set($serial_text[$language->getCustom()])->nohtml()->altsubstr(0, 200)->get();
            $link = "news/";
            if($result['path'] != null) {
                $link .= $result['path']."/";
            }
            $link .= $result['link'];
            $compiled_body .= $template->assign(array('search_link', 'search_title', 'search_snippet'), array($link, $title[$language->getCustom()], $text), $theme_body);
        }
        if($compiled_body == null) {
            $compiled_body = $template->stringNotify('error', $language->get('search_nothing_found'));
        }
        return $compiled_body;
    }

    private function searchOnPage($query)
    {
        global $database, $constant, $language, $template, $framework;
        $theme_body = $template->get('search_body', 'components/search/');
        $queryBuild = '%'.$query.'%';
        $stmt = $database->con()->prepare("SELECT title,text,pathway FROM {$constant->db['prefix']}_com_static WHERE text like ? OR title like ? LIMIT 50");
        $stmt->bindParam(1, $queryBuild, PDO::PARAM_STR);
        $stmt->bindParam(2, $queryBuild, PDO::PARAM_STR);
        $stmt->execute();
        $compiled_body = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $serial_text = unserialize($result['text']);
            $text = $framework->set($serial_text[$language->getCustom()])->nohtml()->altsubstr(0, 200)->get();
            $link = "static/".$result['pathway'];
            $compiled_body .= $template->assign(array('search_link', 'search_title', 'search_snippet'), array($link, $title[$language->getCustom()], $text), $theme_body);
        }
        if($compiled_body == null) {
            $compiled_body = $template->stringNotify('error', $language->get('search_nothing_found'));
        }
        return $compiled_body;

    }
}


?>