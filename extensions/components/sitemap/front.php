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

class components_sitemap_front {
    protected static $instance = null;
    private $map = array();

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Add info to sitemap. Example: extension::getInstance()->call(extension::TYPE_COMPONENT, 'sitemap')->add('/test.html', date('c'), 'daily', 0.5)
     * @param $uri
     * @param $date
     * @param $freq
     * @param $priority
     */
    public function add($uri, $date, $freq, $priority) {
        $this->map[] = array(
            'uri' => property::getInstance()->get('url').$uri,
            'date' => $date,
            'freq' => $freq,
            'priority' => $priority
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
        }
    }

    private function loadDefaults() {
        $this->add('/', date('c'), 'daily', '1.0'); // main page
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
            $this->add($link, date('c', $result['date']), 'weekly', '0.3');
        }
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT `pathway`, `date` FROM ".property::getInstance()->get('db_prefix')."_com_static");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->add("/static/".$result['pathway'], date('c', $result['date']), 'weekly', '0.4');
        }
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT id FROM ".property::getInstance()->get('db_prefix')."_user WHERE aprove = 0");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->add("/user/id".$result['id'], date('c'), 'weekly', '0.2');
        }
        $stmt = null;
        $stmt = database::getInstance()->con()->prepare("SELECT a.path, b.`date` FROM `".property::getInstance()->get('db_prefix')."_com_news_category` a, `".property::getInstance()->get('db_prefix')."_com_news_entery` b WHERE a.category_id = b.category AND a.path != '' GROUP BY a.path ORDER BY b.`date` DESC");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->add("/news/".$result['path'], date('c', $result['date']), 'weekly', '0.3');
        }
    }
}