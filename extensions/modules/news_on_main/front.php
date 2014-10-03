<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\extension;
use engine\database;
use engine\property;
use engine\system;
use engine\language;
use engine\user;
use engine\template;

class modules_news_on_main_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $viewTags = extension::getInstance()->getConfig('enable_tags', 'news', 'components', 'boolean');
        $viewCount = extension::getInstance()->getConfig('enable_views_count', 'news', 'components', 'boolean');
        $params = array();
        $time = time();
        $page_news_count = extension::getInstance()->getConfig('count_news_page', 'news', 'components', 'int');
        $max_preview_length = extension::getInstance()->getConfig('short_news_length', 'news', 'components', 'int');
        $stmt = null;
        $content = null;
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM " . property::getInstance()->get('db_prefix') . "_com_news_entery a,
												  " . property::getInstance()->get('db_prefix') . "_com_news_category b
												  WHERE a.date <= ?
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT 0,?");
        $stmt->bindParam(1, $time, PDO::PARAM_INT);
        $stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $lang_text = system::getInstance()->altstripslashes(unserialize($result['text']));
            $lang_title = system::getInstance()->altstripslashes(unserialize($result['title']));
            $lang_keywords = unserialize($result['keywords']);
            $news_short_text = $lang_text[language::getInstance()->getUseLanguage()];
            if(system::getInstance()->length($lang_title[language::getInstance()->getUseLanguage()]) < 1) // do not add the empty title news
                continue;
            if (system::getInstance()->contains('<hr />', $news_short_text)) {
                $news_short_text = strstr($news_short_text, '<hr />', true);
            } elseif (system::getInstance()->length($news_short_text) > $max_preview_length) {
                $news_short_text = system::getInstance()->sentenceSub($news_short_text, $max_preview_length) . "...";
            }
            $urlfix_object = extension::getInstance()->call(extension::TYPE_HOOK, 'urlfixer');
            if(is_object($urlfix_object))
                $news_short_text = $urlfix_object->fix($news_short_text);
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
                $comment_count = extension::getInstance()->call(extension::TYPE_HOOK, 'comment')->getCount('/'.language::getInstance()->getUseLanguage().'/news/'.$news_full_link);
            $cat_serial_text = unserialize($result['name']);
            $news_view_id = $result['id'];
            $image_poster_root = root . '/upload/news/poster_' . $news_view_id . '.jpg';
            $image_poster_url = false;
            if(file_exists($image_poster_root)) {
                $image_poster_url = property::getInstance()->get('script_url') . '/upload/news/poster_' . $news_view_id . '.jpg';
            }
            $params[] = array(
                'tags' => $tag_array,
                'title' => $lang_title[language::getInstance()->getUseLanguage()],
                'text' => $news_short_text,
                'date' => system::getInstance()->toDate($result['date'], 'h'),
                'unixtime' => $result['date'],
                'category_url' => $result['path'],
                'category_name' => $cat_serial_text[language::getInstance()->getUseLanguage()],
                'author_id' => $result['author'],
                'author_nick' => user::getInstance()->get('nick', $result['author']),
                'full_news_uri' => $news_full_link,
                'comment_count' => $comment_count,
                'view_count' => $result['views'],
                'poster' => $image_poster_url,
                'important' => $result['important']
            );
        }
        $total_news_count = $this->totalNewsCount();
        $pagination = template::getInstance()->showFastPagination(0, $page_news_count, $total_news_count, 'news');
        $data = template::getInstance()->twigRender('/components/news/short_view.tpl',
            array('local' => $params,
                'pagination' => $pagination,
                'cfg' => array(
                    'view_tags' => $viewTags,
                    'view_count' => $viewCount
                )
            )
        );
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $data);
    }

    public function totalNewsCount() {
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_news_entery");
        $stmt->execute();
        $res = $stmt->fetch();
        return $res[0];
    }
}