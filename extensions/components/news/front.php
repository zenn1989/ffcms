<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\template;
use engine\router;
use engine\system;
use engine\database;
use engine\property;
use engine\language;
use engine\extension;
use engine\meta;
use engine\user;

class components_news_front {
    protected static $instance = null;
    const ALLOWED_HTML_TAGS = "<p><a><img><img/><table><tr><td><tbody><thead><th><pre><iframe><span><strong><em><s><blockquote><ul><ol><li><h1><h2><h3><h4><div>";

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function make() {
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $this->buildNews());
    }

    private function buildNews() {
        $content = null;
        $way = $source_way = router::getInstance()->shiftUriArray();
        // get latest object
        $last_object = array_pop($way);
        if($way[0] == "tag" && system::getInstance()->suffixEquals($last_object, '.html')) {
            $content = $this->viewTagList($last_object);
        } elseif($source_way[0] == "add" && extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'bol')) {
            if($last_object < 1)
                $content = $this->viewUseraddNews();
            else
                $content = $this->viewUsereditNews($last_object);
        } elseif (system::getInstance()->suffixEquals($last_object, '.html')) { // its a single news
            $content = $this->viewFullNews($last_object, $way);
        } else { // its a category
            $content = $this->viewCategory();
        }
        return $content;
    }

    private function viewUsereditNews($news_id) {
        $user_id = user::getInstance()->get('id');
        if($user_id < 1 || !$this->checkNewsOwnerExist($user_id, $news_id))
            return null;
        $params = array();

        $params['cfg']['captcha_full'] = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false;
        $params['captcha'] = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();

        if(system::getInstance()->post('save')) {
            $editor_id = user::getInstance()->get('id');
            $title = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $category_id = system::getInstance()->post('category');
            $pathway = system::getInstance()->nohtml(system::getInstance()->post('pathway')) . ".html";
            $text = array();
            foreach(system::getInstance()->post('text') as $news_lang=>$news_text) {
                $text[$news_lang] = system::getInstance()->safeHtml($news_text, self::ALLOWED_HTML_TAGS);
            }
            $description = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $keywords = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            if (!extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate(system::getInstance()->post('captcha'))) {
                $params['notify']['captcha_error'] = true;
            }
            if (strlen($title[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            }
            if (!system::getInstance()->isInt($category_id)) {
                $params['notify']['nocat'] = true;
            }
            if (strlen($pathway) < 1 || !$this->checkNewsWay($pathway, $news_id, $category_id)) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($text[property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if(sizeof($params['notify']) == 0) {
                $serial_title = serialize($title);
                $serial_text = serialize($text);
                $serial_description = serialize($description);
                $serial_keywords = serialize($keywords);
                $stmt = database::getInstance()->con()->prepare("UPDATE " . property::getInstance()->get('db_prefix') . "_com_news_entery SET title = ?, text = ?, link = ?,
						category = ?, date = ?, author = ?, description = ?, keywords = ? WHERE id = ?");
                $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                $stmt->bindParam(3, $pathway, PDO::PARAM_STR);
                $stmt->bindParam(4, $category_id, PDO::PARAM_INT);
                $stmt->bindParam(5, $date, PDO::PARAM_INT);
                $stmt->bindParam(6, $editor_id, PDO::PARAM_INT);
                $stmt->bindParam(7, $serial_description, PDO::PARAM_STR);
                $stmt->bindParam(8, $serial_keywords, PDO::PARAM_STR);
                $stmt->bindParam(9, $news_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE `object_type` = 'news' AND `object_id` = ?");
                $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                foreach($keywords as $keyrow) {
                    $keyrow_array = system::getInstance()->altexplode(',', $keyrow);
                    foreach($keyrow_array as $objectkey) {
                        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'news', ?)");
                        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
                        $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                    }
                }
                $params['notify']['success'] = true;
                if($_FILES['newsimage']['size'] > 0) {
                    $dx = extension::getInstance()->getConfig('poster_dx', 'news', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'news', extension::TYPE_COMPONENT, 'int');
                    $save_name = 'poster_' . $news_id . '.jpg';
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/news/', $_FILES['newsimage'], $dx, $dy, $save_name);
                }
            }
        }

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id = ? AND author = ? AND display = 0");
        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if($result = $stmt->fetch()) {
            $params['news']['categorys'] = $this->getCategoryArray();
            $params['news']['id'] = $news_id;
            $params['news']['title'] = unserialize($result['title']);
            $params['news']['text'] = unserialize($result['text']);
            $params['news']['pathway'] = system::getInstance()->noextention($result['link']);
            $params['news']['cat_id'] = $result['category'];
            $params['news']['date'] = system::getInstance()->toDate($result['date'], 'h');
            $params['news']['description'] = unserialize($result['description']);
            $params['news']['keywords'] = unserialize($result['keywords']);
            if(file_exists(root . '/upload/news/poster_' . $news_id . '.jpg')) {
                $params['news']['poster_path'] = '/upload/news/poster_' . $news_id . '.jpg';
                $params['news']['poster_name'] = 'poster_' . $news_id . '.jpg';
            }
        } else {
            return null;
        }
        return template::getInstance()->twigRender('components/news/add_edit.tpl', $params);
    }

    private function viewUseraddNews() {
        if(user::getInstance()->get('id') < 1)
            return null;
        $params = array();
        $params['cfg']['captcha_full'] = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false;
        $params['captcha'] = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();
        $params['news']['categorys'] = $this->getCategoryArray();
        if(system::getInstance()->post('save')) {
            $editor_id = user::getInstance()->get('id');
            $params['news']['title'] = system::getInstance()->nohtml(system::getInstance()->post('title'));
            $params['news']['cat_id'] = system::getInstance()->post('category');
            $params['news']['pathway'] = system::getInstance()->nohtml(system::getInstance()->post('pathway'));
            $pathway = $params['news']['pathway'] . ".html";
            foreach(system::getInstance()->post('text') as $news_lang=>$news_text) {
                $params['news']['text'][$news_lang] = system::getInstance()->safeHtml($news_text, self::ALLOWED_HTML_TAGS);
            }
            $params['news']['description'] = system::getInstance()->nohtml(system::getInstance()->post('description'));
            $params['news']['keywords'] = system::getInstance()->nohtml(system::getInstance()->post('keywords'));
            $date = system::getInstance()->post('current_date') == "on" ? time() : system::getInstance()->toUnixTime(system::getInstance()->post('date'));
            $params['news']['date'] = system::getInstance()->toDate($date, 'h');
            if (!extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate(system::getInstance()->post('captcha'))) {
                $params['notify']['captcha_error'] = true;
            }
            if (strlen($params['news']['title'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notitle'] = true;
            }
            if (!system::getInstance()->isInt($params['news']['cat_id'])) {
                $params['notify']['nocat'] = true;
            }
            if (strlen($pathway) < 1 || !$this->checkNewsWay($pathway, 0, $params['news']['cat_id'])) {
                $params['notify']['wrongway'] = true;
            }
            if (strlen($params['news']['text'][property::getInstance()->get('lang')]) < 1) {
                $params['notify']['notext'] = true;
            }
            if (sizeof($params['notify']) == 0) {
                $serial_title = serialize($params['news']['title']);
                $serial_text = serialize($params['news']['text']);
                $serial_description = serialize($params['news']['description']);
                $serial_keywords = serialize($params['news']['keywords']);
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_news_entery
					(`title`, `text`, `link`, `category`, `date`, `author`, `description`, `keywords`, `display`, `important`) VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, 0, 0)");
                $stmt->bindParam(1, $serial_title, PDO::PARAM_STR);
                $stmt->bindParam(2, $serial_text, PDO::PARAM_STR);
                $stmt->bindParam(3, $pathway, PDO::PARAM_STR);
                $stmt->bindParam(4, $params['news']['cat_id'], PDO::PARAM_INT);
                $stmt->bindParam(5, $date, PDO::PARAM_STR);
                $stmt->bindParam(6, $editor_id, PDO::PARAM_INT);
                $stmt->bindParam(7, $serial_description, PDO::PARAM_STR);
                $stmt->bindParam(8, $serial_keywords, PDO::PARAM_STR);
                $stmt->execute();
                $new_news_id = database::getInstance()->con()->lastInsertId();
                $stmt = null;
                foreach($params['news']['keywords'] as $keyrow) {
                    $keyrow_array = system::getInstance()->altexplode(',', $keyrow);
                    foreach($keyrow_array as $objectkey) {
                        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_mod_tags(`object_id`, `object_type`, `tag`) VALUES (?, 'news', ?)");
                        $stmt->bindParam(1, $new_news_id, PDO::PARAM_INT);
                        $stmt->bindParam(2, $objectkey, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt = null;
                    }
                }
                // image poster for news
                if($_FILES['newsimage']['size'] > 0) {
                    $save_name = 'poster_' . $new_news_id . '.jpg';
                    $dx = extension::getInstance()->getConfig('poster_dx', 'news', extension::TYPE_COMPONENT, 'int');
                    $dy = extension::getInstance()->getConfig('poster_dy', 'news', extension::TYPE_COMPONENT, 'int');
                    extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadResizedImage('/news/', $_FILES['newsimage'], $dx, $dy, $save_name);
                }
                system::getInstance()->redirect('/user/id' . $editor_id . '/news/');
            }
        }
        return template::getInstance()->twigRender('components/news/add_edit.tpl', $params);
    }

    private function viewFullNews($url, $categories)
    {
        $viewTags = extension::getInstance()->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = extension::getInstance()->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $stmt = null;
        $category_link = null;
        $category_text = null;
        $link_cat = system::getInstance()->altimplode("/", $categories);
        $time = time();
        $catstmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE path = ?");
        $catstmt->bindParam(1, $link_cat, PDO::PARAM_STR);
        $catstmt->execute();
        if ($catresult = $catstmt->fetch()) {
            $category_link = $catresult['path'];
            $category_serial_text = unserialize($catresult['name']);
            $category_text = $category_serial_text[language::getInstance()->getUseLanguage()];
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE link = ? AND category = ? AND display = 1 AND date <= ?");
            $stmt->bindParam(1, $url, PDO::PARAM_STR);
            $stmt->bindParam(2, $catresult['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(3, $time, PDO::PARAM_INT);
            $stmt->execute();
        }
        if ($stmt != null && $result = $stmt->fetch()) {
            $news_view_id = $result['id'];
            $lang_text = unserialize($result['text']);
            $lang_title = unserialize($result['title']);
            $lang_description = unserialize($result['description']);
            $lang_keywords = unserialize($result['keywords']);
            if(system::getInstance()->length($lang_title[language::getInstance()->getUseLanguage()]) < 1 || system::getInstance()->length($lang_text[language::getInstance()->getUseLanguage()]) < 1)
                return null;
            meta::getInstance()->add('title', $lang_title[language::getInstance()->getUseLanguage()]);
            meta::getInstance()->add('keywords', $lang_keywords[language::getInstance()->getUseLanguage()]);
            meta::getInstance()->add('description', $lang_description[language::getInstance()->getUseLanguage()]);
            $tagPrepareArray = system::getInstance()->altexplode(',', $lang_keywords[language::getInstance()->getUseLanguage()]);
            $tag_array = array();
            foreach($tagPrepareArray as $tagItem) {
                $tag_array[] = trim($tagItem);
            }
            $similar_array = array();
            $search_similar_string = $lang_title[language::getInstance()->getUseLanguage()];
            $stmt = null;
            $stmt = database::getInstance()->con()->prepare("SELECT a.*, b.path, MATCH (a.title) AGAINST (? IN BOOLEAN MODE) AS relevance
                                        FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a,
                                        ".property::getInstance()->get('db_prefix')."_com_news_category b
                                        WHERE a.category = b.category_id AND a.id != ? AND a.display = 1
                                        AND MATCH (a.title) AGAINST (? IN BOOLEAN MODE)
                                        ORDER BY relevance LIMIT 0,5");
            $stmt->bindParam(1, $search_similar_string, PDO::PARAM_STR);
            $stmt->bindParam(2, $news_view_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $search_similar_string, PDO::PARAM_STR);
            $stmt->execute();
            $simBody = null;
            if($stmt->rowCount() > 0) {
                $simRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($simRes as $simRow) {
                    $similar_title = unserialize($simRow['title']);
                    $similar_path = $simRow['path'];
                    $similar_full_path = $similar_path == null ? $simRow['link'] : $similar_path . "/" . $simRow['link'];
                    $similar_text_serialize = unserialize($simRow['text']);
                    $similar_text_full = system::getInstance()->nohtml($similar_text_serialize[language::getInstance()->getUseLanguage()]);
                    $similar_text_short = system::getInstance()->sentenceSub($similar_text_full, 200);
                    $similar_array[] = array(
                        'link' => $similar_full_path,
                        'title' => $similar_title[language::getInstance()->getUseLanguage()],
                        'preview' => $similar_text_short
                    );
                }
            }

            if($viewCount) {
                $vstmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_news_entery SET views = views+1 WHERE id = ?");
                $vstmt->bindParam(1, $news_view_id, PDO::PARAM_INT);
                $vstmt->execute();
                $vstmt = null;
            }
            $image_poster_root = root . '/upload/news/poster_' . $news_view_id . '.jpg';
            $image_poster_url = false;
            if(file_exists($image_poster_root)) {
                $image_poster_url = property::getInstance()->get('script_url') . '/upload/news/poster_' . $news_view_id . '.jpg';
            }
            $image_gallery_root = root . '/upload/news/gallery/' . $news_view_id . '/';
            $image_gallery_array = array();
            if(file_exists($image_gallery_root)) {
                foreach(scandir($image_gallery_root . 'orig/') as $image_item) {
                    $file_array = explode(".", $image_item);
                    $file_ext = array_pop($file_array);
                    if(in_array($file_ext, array('jpg', 'gif', 'png', 'bmp', 'jpeg'))) {
                        $image_gallery_array[] = array(
                            'full' => property::getInstance()->get('script_url') . '/upload/news/gallery/' . $news_view_id . '/orig/' . $image_item,
                            'thumb' => property::getInstance()->get('script_url') . '/upload/news/gallery/' . $news_view_id . '/thumb/' . $image_item
                        );
                    }
                }
            }
            $comment_list = extension::getInstance()->call(extension::TYPE_MODULE, 'comments')->buildCommentTemplate();
            $theme_array = array(
                'tags' => $tag_array,
                'title' => $lang_title[language::getInstance()->getUseLanguage()],
                'text' => system::getInstance()->removeCharsFromString('<hr />', $lang_text[language::getInstance()->getUseLanguage()], 1),
                'date' => system::getInstance()->toDate($result['date'], 'h'),
                'category_url' => $category_link,
                'category_name' => $category_text,
                'author_id' => $result['author'],
                'author_nick' => user::getInstance()->get('nick', $result['author']),
                'view_count' => $result['views'],
                'similar_items' => $similar_array,
                'pathway' => router::getInstance()->getUriString(),
                'cfg' => array(
                    'view_tags' => $viewTags,
                    'view_count' => $viewCount
                ),
                'gallery' => $image_gallery_array,
                'poster' => $image_poster_url
            );
            return template::getInstance()->twigRender('components/news/full_view.tpl', array('local' => $theme_array, 'comments' => $comment_list));
        }
        return null;
    }

    private function viewTagList($tagname)
    {
        $cleartag = system::getInstance()->nohtml(substr($tagname, 0, -5));
        meta::getInstance()->add('title', $cleartag);
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.category = b.category_id AND a.keywords like ? LIMIT 100");
        $buildSearch = '%'.$cleartag.'%';
        $stmt->bindParam(1, $buildSearch, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() < 1){
            return null;
        }
        $prepared_array = array('tagname' => $cleartag);
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $news_full_link = null;
            if ($result['path'] == null) {
                $news_full_link = $result['link'];
            } else {
                $news_full_link = $result['path'] . "/" . $result['link'];
            }
            $news_serial_title = unserialize($result['title']);
            $prepared_array['newsinfo'][] = array('link' => $news_full_link, 'title' => $news_serial_title[language::getInstance()->getUseLanguage()]);
        }
        return template::getInstance()->twigRender('components/news/tag_view.tpl', array('local' => $prepared_array));
    }

    public function viewCategory()
    {
        $viewTags = extension::getInstance()->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = extension::getInstance()->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $way = router::getInstance()->shiftUriArray();
        $pop_array = $way;
        $last_item = array_pop($pop_array);
        $page_index = 0;
        $page_news_count = extension::getInstance()->getConfig('count_news_page', 'news', 'components', 'int');
        $total_news_count = 0;
        $cat_link = null;
        if (system::getInstance()->isInt($last_item)) {
            $page_index = $last_item;
            $cat_link = system::getInstance()->altimplode("/", $pop_array);
        } else {
            $cat_link = system::getInstance()->altimplode("/", $way);
        }
        $select_coursor_start = $page_index * $page_news_count;

        $category_select_array = array();
        $category_list = null;
        $fstmt = null;
        $page_title = null;
        if (extension::getInstance()->getConfig('multi_category', 'news', 'components', 'boolean')) {
            $fstmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE path like ?");
            $path_swarm = "$cat_link%";
            $fstmt->bindParam(1, $path_swarm, PDO::PARAM_STR);
            $fstmt->execute();
        } else {
            $fstmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_category WHERE path = ?");
            $fstmt->bindParam(1, $cat_link, PDO::PARAM_STR);
            $fstmt->execute();
        }
        while ($fresult = $fstmt->fetch()) {
            $category_select_array[] = $fresult['category_id'];
            if ($cat_link == $fresult['path']) {
                $serial_name = unserialize($fresult['name']);
                meta::getInstance()->add('title', $page_title = language::getInstance()->get('news_view_category').': '.$serial_name[language::getInstance()->getUseLanguage()]);
            }
        }
        $category_list = system::getInstance()->altimplode(',', $category_select_array);
        $theme_array = array();
        $fstmt = null;
        if (system::getInstance()->isIntList($category_list)) {
            $max_preview_length = extension::getInstance()->getConfig('short_news_length', 'news', 'components', 'int');
            $time = time();
            $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE category in ($category_list) AND date <= ? AND display = 1");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->execute();
            if ($countRows = $stmt->fetch()) {
                $total_news_count = $countRows[0];
            }
            $stmt = null;
            // TODO: remove delay_news_public from admin panel!!!
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a,
												  ".property::getInstance()->get('db_prefix')."_com_news_category b
												  WHERE a.category in ($category_list) AND a.date <= ?
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT ?,?");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->bindParam(2, $select_coursor_start, PDO::PARAM_INT);
            $stmt->bindParam(3, $page_news_count, PDO::PARAM_INT);
            $stmt->execute();
            if (sizeof($category_select_array) > 0) {
                while ($result = $stmt->fetch()) {
                    $lang_text = unserialize($result['text']);
                    $lang_title = unserialize($result['title']);
                    $lang_keywords = unserialize($result['keywords']);
                    $news_short_text = $lang_text[language::getInstance()->getUseLanguage()];
                    if(system::getInstance()->length($lang_title[language::getInstance()->getUseLanguage()]) < 1) // do not add the empty title news
                        continue;
                    if (system::getInstance()->contains('<hr />', $news_short_text)) {
                        $news_short_text = strstr($news_short_text, '<hr />', true);
                    } elseif (system::getInstance()->length($news_short_text) > $max_preview_length) {
                        $news_short_text = system::getInstance()->sentenceSub($news_short_text, $max_preview_length) . "...";
                    }
                    if ($result['path'] == null) {
                        $news_full_link = $result['link'];
                    } else {
                        $news_full_link = $result['path'] . "/" . $result['link'];
                    }
                    $tagPrepareArray = system::getInstance()->altexplode(',', $lang_keywords[language::getInstance()->getUseLanguage()]);
                    $tag_array = array();
                    foreach($tagPrepareArray as $tagItem) {
                        $tag_array[] = trim($tagItem);
                    }
                    $comment_count = 0;
                    if(is_object(extension::getInstance()->call(extension::TYPE_HOOK, 'comment')))
                        $comment_count = extension::getInstance()->call(extension::TYPE_HOOK, 'comment')->getCount('/news/'.$news_full_link);
                    $cat_serial_text = unserialize($result['name']);
                    $news_view_id = $result['id'];
                    $image_poster_root = root . '/upload/news/poster_' . $news_view_id . '.jpg';
                    $image_poster_url = false;
                    if(file_exists($image_poster_root)) {
                        $image_poster_url = property::getInstance()->get('script_url') . '/upload/news/poster_' . $news_view_id . '.jpg';
                    }
                    $theme_array[] = array(
                        'tags' => $tag_array,
                        'title' => $lang_title[language::getInstance()->getUseLanguage()],
                        'text' => $news_short_text,
                        'date' => system::getInstance()->toDate($result['date'], 'h'),
                        'category_url' => $result['path'],
                        'category_name' => $cat_serial_text[language::getInstance()->getUseLanguage()],
                        'author_id' => $result['author'],
                        'author_nick' => user::getInstance()->get('nick', $result['author']),
                        'full_news_uri' => $news_full_link,
                        'comment_count' => $comment_count,
                        'view_count' => $result['views'],
                        'poster' => $image_poster_url
                    );
                }
            }
            $stmt = null;
        }
        $page_link = $cat_link == null ? "news" : "news/" . $cat_link;
        $pagination = template::getInstance()->showFastPagination($page_index, $page_news_count, $total_news_count, $page_link);
        return template::getInstance()->twigRender('/components/news/short_view.tpl',
            array('local' => $theme_array,
                'pagination' => $pagination,
                'cfg' => array(
                    'view_tags' => $viewTags,
                    'view_count' => $viewCount
                ),
                'page_title' => $page_title
            )
        );
    }

    /**
     * Magic and drugs inside (:
     * @return array
     */
    public function getCategoryArray() {
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_category ORDER BY `path` ASC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $work_data = array();
        $total_result = array();
        foreach($result as $item) {
            $work_data[$item['path']] = array(
                'id' => $item['category_id'],
                'name' => $item['name']
            );
        }
        ksort($work_data); // sort
        foreach($work_data as $path=>$row) {
            $cname = unserialize($row['name']);
            $spliter_count = substr_count($path, "/");
            $add = '';
            if ($path != null) {
                for ($i = -1; $i <= $spliter_count; $i++) {
                    $add .= "-";
                }
            } else {
                $add = "-";
            }
            $total_result[] = array(
                'id' => $row['id'],
                'name' => $add . ' ' . $cname[language::getInstance()->getUseLanguage()],
                'path' => $path
            );
        }
        return $total_result;
    }

    public function checkNewsWay($way, $id = 0, $cat_id)
    {
        if (preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way) || $way == "tag") {
            return false;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE link = ? AND category = ? AND id != ?");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->bindParam(2, $cat_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $id, PDO::PARAM_INT);
        $stmt->execute();
        $pRes = $stmt->fetch();
        $stmt = null;
        return $pRes[0] > 0 ? false : true;
    }

    public function checkNewsOwnerExist($owner_id, $news_id) {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery WHERE id = ? AND author = ? AND display = 0");
        $stmt->bindParam(1, $news_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $owner_id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res > 0;
    }
}