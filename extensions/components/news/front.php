<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

if (!extension::registerPathWay('news', 'news')) {
    exit("Component static cannot be registered!");
}

class com_news_front implements com_front
{
    public function load()
    {
        global $engine;
        $content = null;
        $way = $engine->page->shiftPathway();
        // ищем последний элемент
        $last_object = array_pop($way);
        // на всякий сохраняем массив категорий
        $category_array = $way;
        if($way[0] == "tag" && $engine->system->suffixEquals($last_object, '.html')) {
            $content = $this->viewTagList($last_object);
        }
        // это одиночная статлья
        elseif ($engine->system->suffixEquals($last_object, '.html')) {
            $content = $this->viewFullNews($last_object, $category_array);
        } // иначе это содержимое категории
        else {
            $content = $this->viewCategory();
        }
        if ($content == null)
            $content = $engine->template->compile404();
        $engine->page->setContentPosition('body', $content);
    }

    private function viewTagList($tagname)
    {
        global $engine;
        $cleartag = $engine->system->nohtml(substr($tagname, 0, -5));
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery a, {$engine->constant->db['prefix']}_com_news_category b WHERE a.category = b.category_id AND a.keywords like ? LIMIT 100");
        $buildSearch = '%'.$cleartag.'%';
        $stmt->bindParam(1, $buildSearch, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() < 1){
            return null;
        }
        $theme_head = $engine->template->get('tag_search_head', 'components/news/');
        $theme_body = $engine->template->get('tag_search_body', 'components/news/');
        $compiled_result = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $news_full_link = null;
            if ($result['path'] == null) {
                $news_full_link = $result['link'];
            } else {
                $news_full_link = $result['path'] . "/" . $result['link'];
            }
            $news_serial_title = unserialize($result['title']);
            $compiled_result .= $engine->template->assign(array('news_url', 'news_title'), array($news_full_link, $news_serial_title[$engine->language->getCustom()]), $theme_body);
        }
        return $engine->template->assign(array('news_tag_name', 'news_tag_entery'), array($cleartag, $compiled_result), $theme_head);
    }

    public function viewFullNews($url, $categories)
    {
        global $engine;
        $viewTags = $engine->extension->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = $engine->extension->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $stmt = null;
        $category_link = null;
        $category_text = null;
        $link_cat = $engine->system->altimplode("/", $categories);
        $time = time();
        if ($link_cat != null) {
            $engine->rule->add('com.news.have_category', true);
        }
        if($viewTags) {
            $engine->rule->add('com.news.tag', true);
        }
        if($viewCount) {
            $engine->rule->add('com.news.view_count', true);
        }
        $catstmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_category WHERE path = ?");
        $catstmt->bindParam(1, $link_cat, PDO::PARAM_STR);
        $catstmt->execute();
        if ($catresult = $catstmt->fetch()) {
            $category_link = $catresult['path'];
            $category_serial_text = unserialize($catresult['name']);
            $category_text = $category_serial_text[$engine->language->getCustom()];
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery WHERE link = ? AND category = ? AND display = 1 AND date <= ?");
            $stmt->bindParam(1, $url, PDO::PARAM_STR);
            $stmt->bindParam(2, $catresult['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(3, $time, PDO::PARAM_INT);
            $stmt->execute();
        }
        if ($stmt != null && $result = $stmt->fetch()) {
            $news_theme = $engine->template->get('view_full_news', 'components/news/');
            $news_view_id = $result['id'];
            $tag_theme = $engine->template->get('tag_link', 'components/news/');
            $tag_spliter = $engine->template->get('tag_spliter', 'components/news/');
            $lang_text = unserialize($result['text']);
            $lang_title = unserialize($result['title']);
            $lang_description = unserialize($result['description']);
            $lang_keywords = unserialize($result['keywords']);
            $engine->meta->add('title', $lang_title[$engine->language->getCustom()]);
            $engine->meta->set('keywords', $lang_keywords[$engine->language->getCustom()]);
            $engine->meta->set('description', $lang_description[$engine->language->getCustom()]);
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
            if($viewCount) {
                $vstmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_com_news_entery SET views = views+1 WHERE id = ?");
                $vstmt->bindParam(1, $news_view_id, PDO::PARAM_INT);
                $vstmt->execute();
            }
            $news_full_text = $engine->system->removeCharsFromString('<hr />', $lang_text[$engine->language->getCustom()], 1);
            return $engine->template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'js.comment_object', 'js.comment_id', 'js.comment_hash', 'js.comment_strpathway', 'news_tag', 'news_view_count'),
                array($lang_title[$engine->language->getCustom()], $news_full_text, $engine->system->toDate($result['date'], 'h'), $category_link, $category_text, $result['author'], $engine->user->get('nick', $result['author']), 'news', $result['id'], $engine->page->hashFromPathway(), $engine->page->getStrPathway(), $tag_text, $result['views']),
                $news_theme);
        }
        return null;
    }

    public function viewCategory()
    {
        global $engine;
        $viewTags = $engine->extension->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = $engine->extension->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $way = $engine->page->shiftPathway();
        $content = null;
        $pop_array = $way;
        $last_item = array_pop($pop_array);
        $page_index = 0;
        $page_news_count = $engine->extension->getConfig('count_news_page', 'news', 'components', 'int');
        $total_news_count = 0;
        $cat_link = null;
        if($viewTags) {
            $engine->rule->add('com.news.tag', true);
        }
        if($viewCount) {
            $engine->rule->add('com.news.view_count', true);
        }
        if ($engine->system->isInt($last_item)) {
            $page_index = $last_item;
            $cat_link = $engine->system->altimplode("/", $pop_array);
        } else {
            $cat_link = $engine->system->altimplode("/", $way);
        }
        $select_coursor_start = $page_index * $page_news_count;

        $category_select_array = array();
        $category_list = null;
        $fstmt = null;
        if ($engine->extension->getConfig('multi_category', 'news', 'components', 'boolean')) {
            $fstmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_category WHERE path like ?");
            $path_swarm = "$cat_link%";
            $fstmt->bindParam(1, $path_swarm, PDO::PARAM_STR);
            $fstmt->execute();
        } else {
            $fstmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_category WHERE path = ?");
            $fstmt->bindParam(1, $cat_link, PDO::PARAM_STR);
            $fstmt->execute();
        }
        while ($fresult = $fstmt->fetch()) {
            $category_select_array[] = $fresult['category_id'];
            if ($cat_link == $fresult['path']) {
                $serial_name = unserialize($fresult['name']);
                $engine->meta->add('title', $serial_name[$engine->language->getCustom()]);
            }
        }
        $category_list = $engine->system->altimplode(',', $category_select_array);
        $fstmt = null;
        if ($engine->system->isIntList($category_list)) {
            $short_theme = $engine->template->get('view_short_news', 'components/news/');
            $max_preview_length = $engine->extension->getConfig('short_news_length', 'news', 'components', 'int');
            $time = time();
            $stmt = null;
            $cstmt = null;
            if ($engine->extension->getConfig('delay_news_public', 'news', 'components', 'boolean')) {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery a,
												  {$engine->constant->db['prefix']}_com_news_category b
												  WHERE a.category in ($category_list) AND a.date <= ? 
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT ?,?");
                $stmt->bindParam(1, $time, PDO::PARAM_INT);
                $stmt->bindParam(2, $select_coursor_start, PDO::PARAM_INT);
                $stmt->bindParam(3, $page_news_count, PDO::PARAM_INT);
                $stmt->execute();
                $cstmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_entery WHERE category in ($category_list) AND date <= ?");
                $cstmt->bindParam(1, $time, PDO::PARAM_INT);
                $cstmt->execute();
                if ($countRows = $cstmt->fetch()) {
                    $total_news_count = $countRows[0];
                }
                $cstmt = null;
            } else {
                $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery a,
												  {$engine->constant->db['prefix']}_com_news_category b
												  WHERE a.category in ($category_list)
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.id DESC LIMIT ?,?");
                $stmt->bindParam(1, $select_coursor_start, PDO::PARAM_INT);
                $stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
                $stmt->execute();

                $cstmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_com_news_entery WHERE category in ($category_list)");
                $cstmt->execute();
                if ($countRows = $cstmt->fetch()) {
                    $total_news_count = $countRows[0];
                }
                $cstmt = null;
            }
            if (sizeof($category_select_array) > 0) {
                $tag_theme = $engine->template->get('tag_link', 'components/news/');
                $tag_spliter = $engine->template->get('tag_spliter', 'components/news/');
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
            }
            $stmt = null;
        }
        $cstmt = null;
        if ($content != null) {
            $category_theme = $engine->template->get('view_category', 'components/news/');
            $page_link = $cat_link == null ? "news/" : "news/" . $cat_link . "/";
            $content = $engine->template->assign(array('news_body', 'pagination'), array($content, $engine->template->drowNumericPagination($page_index, $page_news_count, $total_news_count, $page_link)), $category_theme);
        }
        return $content;
    }
}

?>