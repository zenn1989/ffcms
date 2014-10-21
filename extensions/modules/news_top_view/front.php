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
use engine\language;
use engine\system;
use engine\template;
use engine\extension;

class modules_news_top_view_front extends \engine\singleton {

    public function make() {
        $params = array();

        $news_count = extension::getInstance()->getConfig('viewtop_count', 'news_top_view', extension::TYPE_MODULE, 'int');
        if($news_count < 1)
            $news_count = 1;

        $day_unixlimit = extension::getInstance()->getConfig('viewtop_days', 'news_top_view', extension::TYPE_MODULE, 'int');
        $day_unixlimit *= 60 * 60 * 24;
        $day_diff = $day_unixlimit === 0 ? 0 : time() - $day_unixlimit;

        $stmt = database::getInstance()->con()->prepare("SELECT a.title,a.link,a.views,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a,".property::getInstance()->get('db_prefix')."_com_news_category b
                        WHERE a.category = b.category_id AND a.display > 0 AND a.date >= ? ORDER BY a.views DESC LIMIT 0,?");
        $stmt->bindParam(1, $day_diff, \PDO::PARAM_INT);
        $stmt->bindParam(2, $news_count, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $row) {
            $full_path = null;
            if(property::getInstance()->get('use_multi_language'))
                $full_path .= '/'.language::getInstance()->getUseLanguage();
            $full_path .= '/news/';
            if($row['path'] != null)
                $full_path .= $row['path'] . '/';
            $full_path .= $row['link'];
            $serial_title = system::getInstance()->altstripslashes(unserialize($row['title']));
            $params['top'][] = array(
                'title' => $serial_title[language::getInstance()->getUseLanguage()],
                'views' => $row['views'],
                'pathway' => $full_path
            );
        }
        $tmp = template::getInstance()->twigRender('modules/news_top_view/list.tpl', array('local' => $params));
        template::getInstance()->set(template::TYPE_MODULE, 'news_top_view', $tmp);
    }
}