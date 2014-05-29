<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\router;
use engine\template;
use engine\database;
use engine\property;
use engine\cache;
use engine\language;
use engine\system;

class components_sitemap_front {
    protected static $instance = null;
    private $map = array();

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Add info to sitemap.
     * @param string $uri
     * @param int $date
     * @param string $freq
     * @param float $priority
     * @param string|null $title
     */
    public function add($uri, $date, $freq, $priority, $title = null) {
        $this->map[] = array(
            'uri' => property::getInstance()->get('url').$uri,
            'date' => $date,
            'freq' => $freq,
            'priority' => $priority,
            'title' => $title
        );
    }

    public function make() {
        $way = router::getInstance()->shiftUriArray();
        $lang = null;
        if(property::getInstance()->get('use_multi_language'))
            $lang = '_' . router::getInstance()->getPathLanguage();

        if($way[0] === "sitemap.xml") {
            header("Content-type: text/xml");
            $render = cache::getInstance()->get('sitemap'.$lang);
            if(is_null($render)) {
                $this->loadDefaults();
                $render = template::getInstance()->twigRender('components/sitemap/map.tpl', array('local' => $this->map));
                cache::getInstance()->store('sitemap'.$lang, $render);
            }
            template::getInstance()->justPrint($render);
        } elseif($way[0] == "sitemap.html") {
            $tpl = cache::getInstance()->get('htmlmap'.$lang);
            if(is_null($tpl)) {
                $this->loadDefaults();
                $tpl = template::getInstance()->twigRender('components/sitemap/html.tpl', array('local' => $this->map));
                cache::getInstance()->store('htmlmap'.$lang, $tpl);
            }
            template::getInstance()->set(template::TYPE_CONTENT, 'body', $tpl);
        }
    }

    private function loadDefaults() {
        $global_title = property::getInstance()->get('seo_title');
        $this->add('/', date('c'), 'daily', '1.0', $global_title[language::getInstance()->getUseLanguage()]); // main page
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_news_entery a, ".
            property::getInstance()->get('db_prefix')."_com_news_category b WHERE a.display = 1 AND a.category = b.category_id ORDER BY a.id ASC");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $link = null;
            if($result['path'] == null) {
                $link = "/news/".$result['link'];
            } else {
                $link = "/news/".$result['path']."/".$result['link'];
            }
            $news_title = system::getInstance()->altstripslashes(unserialize($result['title']));
            $this->add($link, date('c', $result['date']), 'weekly', '0.3', $news_title[language::getInstance()->getUseLanguage()]);
        }
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT `pathway`, `date`, `title` FROM ".property::getInstance()->get('db_prefix')."_com_static");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $static_title = system::getInstance()->altstripslashes(unserialize($result['title']));
            $this->add("/static/".$result['pathway'], date('c', $result['date']), 'weekly', '0.4', $static_title[language::getInstance()->getUseLanguage()]);
        }
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT id,nick,login FROM ".property::getInstance()->get('db_prefix')."_user WHERE aprove = 0");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $u_title = $result['nick'];
            if(system::getInstance()->length($u_title) < 1)
                $u_title = $result['login']; // unsafe .. but didnt care (:
            $this->add("/user/id".$result['id'], date('c'), 'weekly', '0.2', $u_title);
        }
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT a.`path`, a.`name`, b.`date` FROM `".property::getInstance()->get('db_prefix')."_com_news_category` a, `".property::getInstance()->get('db_prefix')."_com_news_entery` b WHERE a.category_id = b.category AND a.path != '' GROUP BY a.path ORDER BY b.`date` DESC");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cat_name = unserialize($result['name']);
            $this->add("/news/".$result['path'], date('c', $result['date']), 'weekly', '0.3', $cat_name[language::getInstance()->getUseLanguage()]);
        }
    }
}