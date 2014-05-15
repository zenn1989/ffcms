<?php

use engine\extension;
use engine\database;
use engine\property;
use engine\language;
use engine\template;
use engine\system;

class modules_news_new_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $params = array();

        $news_count = extension::getInstance()->getConfig('new_count', 'news_new', extension::TYPE_MODULE, 'int');
        if($news_count < 1)
            $news_count = 1;

        $stmt = database::getInstance()->con()->prepare("SELECT a.id,a.title,a.link,b.path FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a,".property::getInstance()->get('db_prefix')."_com_news_category b
                        WHERE a.category = b.category_id AND a.display > 0 ORDER BY a.date DESC LIMIT 0,?");
        $stmt->bindParam(1, $news_count, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $row) {
            $full_path = null;
            $image = null;
            if(property::getInstance()->get('use_multi_language'))
                $full_path .= '/'.language::getInstance()->getUseLanguage();
            $full_path .= '/news/';
            if($row['path'] != null)
                $full_path .= $row['path'] . '/';
            $full_path .= $row['link'];
            $serial_title = system::getInstance()->altstripslashes(unserialize($row['title']));
            if(file_exists(root . '/upload/news/poster_' . $row['id'] . '.jpg'))
                $image = 'poster_' . $row['id'];
            $params['latest'][] = array(
                'title' => $serial_title[language::getInstance()->getUseLanguage()],
                'image' => $image,
                'pathway' => $full_path
            );
        }
        $tmp = template::getInstance()->twigRender('modules/news_new/list.tpl', array('local' => $params));
        template::getInstance()->set(template::TYPE_MODULE, 'news_new', $tmp);
    }
}


