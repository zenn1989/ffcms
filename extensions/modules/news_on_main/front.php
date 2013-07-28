<?php

class mod_news_on_main_front implements mod_front
{
    public function before()
    {
        global $page, $database, $constant, $extension, $template, $system, $user, $hook, $language, $rule;
        $short_theme = $template->get('view_short_news', 'components/news/');
        $viewTags = $extension->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = $extension->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $tag_theme = $template->get('tag_link', 'components/news/');
        $tag_spliter = $template->get('tag_spliter', 'components/news/');
        if($viewTags) {
            $rule->add('com.news.tag', true);
        }
        if($viewCount) {
            $rule->add('com.news.view_count', true);
        }
        $time = time();
        $page_news_count = $extension->getConfig('count_news_page', 'news', 'components', 'int');
        $max_preview_length = $extension->getConfig('short_news_length', 'news', 'components', 'int');
        $stmt = null;
        $content = null;
        if ($extension->getConfig('delay_news_public', 'news', 'components', 'boolean')) {
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.date <= ?
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT 0,?");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
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
            $news_short_text = $lang_text[$language->getCustom()];
            if ($system->contains('<hr />', $news_short_text)) {
                $news_short_text = strstr($news_short_text, '<hr />', true);
            } elseif ($system->length($news_short_text) > $max_preview_length) {
                $news_short_text = $system->sentenceSub($news_short_text, $max_preview_length) . "...";
            }
            if ($result['path'] == null) {
                $news_full_link = $result['link'];
            } else {
                $news_full_link = $result['path'] . "/" . $result['link'];
            }
            $prepareTags = $system->altexplode(',', $lang_keywords[$language->getCustom()]);
            $tag_text = null;
            for($i=0;$i<=sizeof($prepareTags);$i++) {
                $tag_url = urlencode($system->noSpaceOnStartEnd($prepareTags[$i]));
                if($tag_url != null) {
                    $tag_text .= $template->assign(array('news_tag_urlencode', 'news_tag_name'), array($tag_url, $system->noSpaceOnStartEnd($prepareTags[$i])), $tag_theme);
                    if($i < sizeof($prepareTags)-1) {
                        $tag_text .= $tag_spliter;
                    }
                }
            }
            if($tag_text == null) {
                $tag_text = $language->get('news_view_tag_notfind');
            }
            $hashWay = $page->hashFromPathway($system->altexplode('/', $news_full_link));
            $comment_count = $hook->get('comment')->getCount($hashWay);
            $cat_serial_text = unserialize($result['name']);
            $content .= $template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'news_full_link', 'news_comment_count', 'news_tag', 'news_view_count'),
                array($lang_title[$language->getCustom()], $news_short_text, $system->toDate($result['date'], 'h'), $result['path'], $cat_serial_text[$language->getCustom()], $result['author'], $user->get('nick', $result['author']), $news_full_link, $comment_count, $tag_text, $result['views']),
                $short_theme);
        }
        if ($content != null) {
            $content .= $template->drowNumericPagination(0, $page_news_count, $this->totalNews(), "news/");
        } else {
            $content = $language->get('news_not_found');
        }
        $page->setContentPosition('body', $content);
    }

    private function totalNews()
    {
        global $database, $constant;
        $time = time();
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE display = 1 AND date <= ?");
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