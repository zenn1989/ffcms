<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\database;
use engine\property;
use engine\template;
use engine\system;
use engine\language;
use engine\extension;

class modules_news_top_discus_front extends \engine\singleton {

    public function make() {
        $params = array();
        $item_count = extension::getInstance()->getConfig('discus_count', 'news_top_discus', extension::TYPE_MODULE, 'int');
        if($item_count < 1)
            $item_count = 1;
        $day_unixlimit = extension::getInstance()->getConfig('discus_days', 'news_top_discus', extension::TYPE_MODULE, 'int');
        $day_unixlimit *= 60 * 60 * 24;
        $day_diff = $day_unixlimit === 0 ? 0 : time() - $day_unixlimit;

        $stmt = database::getInstance()->con()->prepare("SELECT pathway,COUNT(*) as count FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE moderate = 0 AND time >= ? GROUP BY `pathway` ORDER BY count DESC LIMIT 0,?");
        $stmt->bindParam(1, $day_diff, \PDO::PARAM_INT);
        $stmt->bindParam(2, $item_count, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        $article_url = array();
        $article_cat = array();
        $main_cat = false;
        $comment_key_count = array();
        foreach($result as $item) {
            $comment_key_count[$item['pathway']] = $item['count'];
        }
        foreach(system::getInstance()->extractFromMultyArray('pathway', $result) as $uri_string) {
            $uri = system::getInstance()->altexplode('/', $uri_string);
            if(property::getInstance()->get('use_multi_language'))
                array_shift($uri); // remove /ru/ or /en/.
            array_shift($uri); // remove /news/
            if(sizeof($uri) === 1) {
                $article_url[] = system::getInstance()->altimplode('/', $uri);
                $main_cat = true;
            } else {
                $article_url[] = array_pop($uri);
                $article_cat[] = system::getInstance()->altimplode('/', $uri);
            }
        }
        $article_link_list = "'".system::getInstance()->altimplode('\',\'', $article_url)."'";
        $article_cat_list = null;
        if($main_cat)
            $article_cat_list .= "'',";
        $article_cat_list .= "'".system::getInstance()->altimplode('\',\'', $article_cat)."'";
        $stmt = database::getInstance()->con()->query("SELECT a.title,a.link,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a,".property::getInstance()->get('db_prefix')."_com_news_category b
                        WHERE a.link IN ({$article_link_list}) AND b.path in ({$article_cat_list}) AND a.category = b.category_id");
        $news_result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        $news_data = array();
        foreach($news_result as $row) {
            $full_path = null;
            if(property::getInstance()->get('use_multi_language'))
                $full_path .= '/'.language::getInstance()->getUseLanguage();
            $full_path .= '/news/';
            if($row['path'] != null)
                $full_path .= $row['path'] . '/';
            $full_path .= $row['link'];
            if($comment_key_count[$full_path] > 0) {
                $serial_title = unserialize($row['title']);
                $news_data[$full_path] = array(
                    'title' => $serial_title[language::getInstance()->getUseLanguage()]
                );
            }
        }
        foreach($comment_key_count as $item_path => $item_repeat) {
            if(sizeof($news_data[$item_path]) > 0) {
                $params['top'][] = array(
                    'title' => $news_data[$item_path]['title'],
                    'comments' => $item_repeat,
                    'pathway' => $item_path
                );
            }
        }
        $tmp = template::getInstance()->twigRender('modules/news_top_discus/list.tpl', array('local' => $params));
        template::getInstance()->set(template::TYPE_MODULE, 'news_top_discus', $tmp);
    }
}