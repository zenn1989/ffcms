<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

if (!extension::registerPathWay('sitemap', 'sitemap')) {
    exit("Component sitemap cannot be registered!");
}
page::setNoCache('sitemap');
class com_sitemap_front implements com_front
{
    public function load()
    {
        global $engine;
        $way = $engine->page->getPathway();
        if($way[1] == "sitemap.xml") {
            $this->viewSiteMap();
        }
        return null;
    }

    private function viewSiteMap()
    {
        global $engine;
        $engine->template->overloadCarcase(null);
        header("Content-type: text/xml");
        if(($stored_xml = $engine->cache->getBlock('sitemap', 60*60*24)) != null) {
            exit($stored_xml);
        }
        $loader = new sitemap_alternate($engine->template->get('components/sitemap/header'), $engine->template->get('components/sitemap/item'));
        $loader->add(null, date('c'), 'daily', '1.0');
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_news_entery a, {$engine->constant->db['prefix']}_com_news_category b WHERE a.display = 1 AND a.category = b.category_id ORDER BY a.id ASC");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $link = null;
            if($result['path'] == null) {
                $link = "news/".$result['link'];
            } else {
                $link = "news/".$result['path']."/".$result['link'];
            }
            $loader->add($link, date('c', $result['date']), 'weekly', '0.3');
        }
        $stmt = null;
        $stmt = $engine->database->con()->prepare("SELECT `pathway`, `date` FROM {$engine->constant->db['prefix']}_com_static");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loader->add("static/".$result['pathway'], date('c', $result['date']), 'weekly', '0.4');
        }
        $stmt = null;
        $stmt = $engine->database->con()->prepare("SELECT id FROM {$engine->constant->db['prefix']}_user");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loader->add("user/id".$result['id'], date('c'), 'weekly', '0.2');
        }
        $stmt = null;
        $stmt = $engine->database->con()->prepare("SELECT a.path, b.`date` FROM `{$engine->constant->db['prefix']}_com_news_category` a, `{$engine->constant->db['prefix']}_com_news_entery` b WHERE a.category_id = b.category ORDER BY b.`date` DESC");
        $stmt->execute();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loader->add("news/".$result['path'], date('c', $result['date']), 'weekly', '0.3');
        }
        $result = $loader->result();
        $engine->cache->saveBlock('sitemap', $result);
        echo $result;
    }
}

class sitemap_alternate
{
    private $theme_head = null;
    private $theme_body = null;
    private $compiled_body = null;

    function sitemap_alternate($theme_head, $theme_item)
    {
        $this->theme_head = $theme_head;
        $this->theme_body = $theme_item;
    }

    public function add($url, $modifed, $changefreq, $priority)
    {
        global $engine;
        $this->compiled_body .= $engine->template->assign(array('sitemap_url', 'sitemap_date', 'sitemap_freq', 'sitemap_priority'), array($engine->constant->url."/".$url, $modifed, $changefreq, $priority), $this->theme_body);
        $this->compiled_body .= "\n";
    }

    public function result()
    {
        global $engine;
        return $engine->template->assign('sitemap_urls', $this->compiled_body, $this->theme_head);
    }


}


?>