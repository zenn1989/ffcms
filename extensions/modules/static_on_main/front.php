<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class mod_static_on_main_front implements mod_front
{
    public function before()
    {
        global $engine;
        $saved_id = $engine->extension->getConfig('news_id', 'static_on_main', 'modules', 'int');
        $show_date = $engine->extension->getConfig('show_date', 'static_on_main', 'modules', 'boolean');
        if ($saved_id > 0) {
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_com_static WHERE id = ?");
            $stmt->bindParam(1, $saved_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($result = $stmt->fetch()) {
                $com_theme = $engine->template->get("page", "components/static/");
                $serial_text = unserialize($result['text']);
                $serial_title = unserialize($result['title']);
                $date = $show_date ? $engine->system->toDate($result['date'], 'd') : null;
                $engine->page->setContentPosition('body', $engine->template->assign(array('title', 'text', 'date'), array($serial_title[$engine->language->getCustom()], $serial_text[$engine->language->getCustom()], $date), $com_theme));
            }
        }
    }

    public function after()
    {
    }
}

?>