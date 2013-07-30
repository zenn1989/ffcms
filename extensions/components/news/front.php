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
        global $page, $system, $template;
        $content = null;
        $way = $page->shiftPathway();
        // ищем последний элемент
        $last_object = array_pop($way);
        // на всякий сохраняем массив категорий
        $category_array = $way;
        if($way[0] == "tag" && $system->suffixEquals($last_object, '.html')) {
            $content = $this->viewTagList($last_object);
        }
        // это одиночная статлья
        elseif ($system->suffixEquals($last_object, '.html')) {
            $content = $this->viewFullNews($last_object, $category_array);
        } // иначе это содержимое категории
        else {
            $content = $this->viewCategory();
        }
        if ($content == null)
            $content = $template->compile404();
        $page->setContentPosition('body', $content);
    }

    private function viewTagList($tagname)
    {
        global $database, $constant, $template, $system, $language;
        $cleartag = $system->nohtml(substr($tagname, 0, -5));
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a, {$constant->db['prefix']}_com_news_category b WHERE a.category = b.category_id AND a.keywords like ? LIMIT 100");
        $buildSearch = '%'.$cleartag.'%';
        $stmt->bindParam(1, $buildSearch, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() < 1){
            return null;
        }
        $theme_head = $template->get('tag_search_head', 'components/news/');
        $theme_body = $template->get('tag_search_body', 'components/news/');
        $compiled_result = null;
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $news_full_link = null;
            if ($result['path'] == null) {
                $news_full_link = $result['link'];
            } else {
                $news_full_link = $result['path'] . "/" . $result['link'];
            }
            $news_serial_title = unserialize($result['title']);
            $compiled_result .= $template->assign(array('news_url', 'news_title'), array($news_full_link, $news_serial_title[$language->getCustom()]), $theme_body);
        }
        return $template->assign(array('news_tag_name', 'news_tag_entery'), array($cleartag, $compiled_result), $theme_head);
    }

    public function viewFullNews($url, $categories)
    {
        global $database, $constant, $system, $template, $rule, $user, $page, $meta, $language, $extension;
        $viewTags = $extension->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = $extension->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $stmt = null;
        $category_link = null;
        $category_text = null;
        $link_cat = $system->altimplode("/", $categories);
        $time = time();
        if ($link_cat != null) {
            $rule->add('com.news.have_category', true);
        }
        if($viewTags) {
            $rule->add('com.news.tag', true);
        }
        if($viewCount) {
            $rule->add('com.news.view_count', true);
        }
        $catstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path = ?");
        $catstmt->bindParam(1, $link_cat, PDO::PARAM_STR);
        $catstmt->execute();
        if ($catresult = $catstmt->fetch()) {
            $category_link = $catresult['path'];
            $category_serial_text = unserialize($catresult['name']);
            $category_text = $category_serial_text[$language->getCustom()];
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery WHERE link = ? AND category = ? AND display = 1 AND date <= ?");
            $stmt->bindParam(1, $url, PDO::PARAM_STR);
            $stmt->bindParam(2, $catresult['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(3, $time, PDO::PARAM_INT);
            $stmt->execute();
        }
        if ($stmt != null && $result = $stmt->fetch()) {
            $news_theme = $template->get('view_full_news', 'components/news/');
            $news_view_id = $result['id'];
            $tag_theme = $template->get('tag_link', 'components/news/');
            $tag_spliter = $template->get('tag_spliter', 'components/news/');
            $lang_text = unserialize($result['text']);
            $lang_title = unserialize($result['title']);
            $lang_description = unserialize($result['description']);
            $lang_keywords = unserialize($result['keywords']);
            $meta->add('title', $lang_title[$language->getCustom()]);
            $meta->set('keywords', $lang_keywords[$language->getCustom()]);
            $meta->set('description', $lang_description[$language->getCustom()]);
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
            if($viewCount) {
                $vstmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_news_entery SET views = views+1 WHERE id = ?");
                $vstmt->bindParam(1, $news_view_id, PDO::PARAM_INT);
                $vstmt->execute();
            }
            $news_full_text = $system->removeCharsFromString('<hr />', $lang_text[$language->getCustom()], 1);
            return $template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'js.comment_object', 'js.comment_id', 'js.comment_hash', 'news_tag', 'news_view_count'),
                array($lang_title[$language->getCustom()], $news_full_text, $system->toDate($result['date'], 'h'), $category_link, $category_text, $result['author'], $user->get('nick', $result['author']), 'news', $result['id'], $page->hashFromPathway(), $tag_text, $result['views']),
                $news_theme);
        }
        return null;
    }

    public function viewCategory()
    {
        global $page, $system, $database, $constant, $template, $user, $hook, $extension, $meta, $language, $rule;
        $viewTags = $extension->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = $extension->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $way = $page->shiftPathway();
        $content = null;
        $pop_array = $way;
        $last_item = array_pop($pop_array);
        $page_index = 0;
        $page_news_count = $extension->getConfig('count_news_page', 'news', 'components', 'int');
        $total_news_count = 0;
        $cat_link = null;
        if($viewTags) {
            $rule->add('com.news.tag', true);
        }
        if($viewCount) {
            $rule->add('com.news.view_count', true);
        }
        if ($system->isInt($last_item)) {
            $page_index = $last_item;
            $cat_link = $system->altimplode("/", $pop_array);
        } else {
            $cat_link = $system->altimplode("/", $way);
        }
        $select_coursor_start = $page_index * $page_news_count;

        $category_select_array = array();
        $category_list = null;
        $fstmt = null;
        if ($extension->getConfig('multi_category', 'news', 'components', 'boolean')) {
            $fstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path like ?");
            $path_swarm = "$cat_link%";
            $fstmt->bindParam(1, $path_swarm, PDO::PARAM_STR);
            $fstmt->execute();
        } else {
            $fstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path = ?");
            $fstmt->bindParam(1, $cat_link, PDO::PARAM_STR);
            $fstmt->execute();
        }
        while ($fresult = $fstmt->fetch()) {
            $category_select_array[] = $fresult['category_id'];
            if ($cat_link == $fresult['path']) {
                $serial_name = unserialize($fresult['name']);
                $meta->add('title', $serial_name[$language->getCustom()]);
            }
        }
        $category_list = $system->altimplode(',', $category_select_array);
        $fstmt = null;
        if ($system->isIntList($category_list)) {
            $short_theme = $template->get('view_short_news', 'components/news/');
            $max_preview_length = $extension->getConfig('short_news_length', 'news', 'components', 'int');
            $time = time();
            $stmt = null;
            $cstmt = null;
            if ($extension->getConfig('delay_news_public', 'news', 'components', 'boolean')) {
                $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.category in ($category_list) AND a.date <= ? 
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT ?,?");
                $stmt->bindParam(1, $time, PDO::PARAM_INT);
                $stmt->bindParam(2, $select_coursor_start, PDO::PARAM_INT);
                $stmt->bindParam(3, $page_news_count, PDO::PARAM_INT);
                $stmt->execute();
                $cstmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE category in ($category_list) AND date <= ?");
                $cstmt->bindParam(1, $time, PDO::PARAM_INT);
                $cstmt->execute();
                if ($countRows = $cstmt->fetch()) {
                    $total_news_count = $countRows[0];
                }
                $cstmt = null;
            } else {
                $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.category in ($category_list)
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.id DESC LIMIT ?,?");
                $stmt->bindParam(1, $select_coursor_start, PDO::PARAM_INT);
                $stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
                $stmt->execute();

                $cstmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE category in ($category_list)");
                $cstmt->execute();
                if ($countRows = $cstmt->fetch()) {
                    $total_news_count = $countRows[0];
                }
                $cstmt = null;
            }
            if (sizeof($category_select_array) > 0) {
                $tag_theme = $template->get('tag_link', 'components/news/');
                $tag_spliter = $template->get('tag_spliter', 'components/news/');
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
            }
            $stmt = null;
        }
        $cstmt = null;
        if ($content != null) {
            $category_theme = $template->get('view_category', 'components/news/');
            $page_link = $cat_link == null ? "news/" : "news/" . $cat_link . "/";
            $content = $template->assign(array('news_body', 'pagination'), array($content, $template->drowNumericPagination($page_index, $page_news_count, $total_news_count, $page_link)), $category_theme);
        }
        return $content;
    }
}

?>