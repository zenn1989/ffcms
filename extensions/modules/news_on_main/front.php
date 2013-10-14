<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class mod_news_on_main_front implements mod_front
{
    public function before()
    {
        global $engine;
        $short_theme = $engine->template->get('view_short_news', 'components/news/');
        $viewTags = $engine->extension->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = $engine->extension->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $tag_theme = $engine->template->get('tag_link', 'components/news/');
        $tag_spliter = $engine->template->get('tag_spliter', 'components/news/');
        if($viewTags) {
            $engine->rule->add('com.news.tag', true);
        }
        if($viewCount) {
            $engine->rule->add('com.news.view_count', true);
        }
        $time = time();
        $page_news_count = $engine->extension->getConfig('count_news_page', 'news', 'components', 'int');
        $max_preview_length = $engine->extension->getConfig('short_news_length', 'news', 'components', 'int');
        $stmt = null;
        $content = null;
        if ($engine->extension->getConfig('delay_news_public', 'news', 'components', 'boolean')) {
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery a,
												  {$engine->constant->db['prefix']}_com_news_category b
												  WHERE a.date <= ?
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT 0,?");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery a,
												  {$engine->constant->db['prefix']}_com_news_category b
												  WHERE a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.id DESC LIMIT 0,?");
            $stmt->bindParam(1, $page_news_count, PDO::PARAM_INT);
            $stmt->execute();
        }
        while ($result = $stmt->fetch()) {
            $lang_text = unserialize($result['text']);
            $lang_title = unserialize($result['title']);
            $lang_keywords = unserialize($result['keywords']);
            $news_short_text = $lang_text[$engine->language->getCustom()];
            if ($engine->system->contains('<hr />', $news_short_text)) {
                $news_short_text = strstr($news_short_text, '<hr />', true);
            } elseif ($engine->system->length($news_short_text) > $max_preview_length) {
                $news_short_text = $engine->system->sentenceSub($news_short_text, $max_preview_length) . "...";
            }
            if ($result['path'] == null) {
                $news_full_link = $result['link'];
            } else {
                $news_full_link = $result['path'] . "/" . $result['link'];
            }
            $prepareTags = $engine->system->altexplode(',', $lang_keywords[$engine->language->getCustom()]);
            $tag_text = null;
            for($i=0;$i<=sizeof($prepareTags);$i++) {
                $tag_url = urlencode($engine->system->noSpaceOnStartEnd($prepareTags[$i]));
                if($tag_url != null) {
                    $tag_text .= $engine->template->assign(array('news_tag_urlencode', 'news_tag_name'), array($tag_url, $engine->system->noSpaceOnStartEnd($prepareTags[$i])), $tag_theme);
                    if($i < sizeof($prepareTags)-1) {
                        $tag_text .= $tag_spliter;
                    }
                }
            }
            if($tag_text == null) {
                $tag_text = $engine->language->get('news_view_tag_notfind');
            }
            $hashWay = $engine->page->hashFromPathway($engine->system->altexplode('/', $news_full_link));
            $comment_count = $engine->hook->get('comment')->getCount($hashWay);
            $cat_serial_text = unserialize($result['name']);
            $content .= $engine->template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'news_full_link', 'news_comment_count', 'news_tag', 'news_view_count'),
                array($lang_title[$engine->language->getCustom()], $news_short_text, $engine->system->toDate($result['date'], 'h'), $result['path'], $cat_serial_text[$engine->language->getCustom()], $result['author'], $engine->user->get('nick', $result['author']), $news_full_link, $comment_count, $tag_text, $result['views']),
                $short_theme);
        }
        if ($content != null) {
            $content .= $engine->template->drowNumericPagination(0, $page_news_count, $this->totalNews(), "news/");
        } else {
            $content = $engine->language->get('news_not_found');
        }
        $engine->page->setContentPosition('body', $content);
    }

    private function totalNews()
    {
        global $engine;
        $time = time();
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_entery WHERE display = 1 AND date <= ?");
        $stmt->bindParam(1, $time, PDO::PARAM_INT);
        $stmt->execute();
        $re = $stmt->fetch();
        return $re[0];
    }

    public function after()
    {
    }
}

?>