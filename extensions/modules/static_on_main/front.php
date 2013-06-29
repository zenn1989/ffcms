<?php
class mod_static_on_main_front implements mod_front
{
    public function before()
    {
        global $template, $extension, $database, $constant, $page;
        $saved_id = $extension->getConfig('news_id', 'static_on_main', 'modules', 'int');
        $show_date = $extension->getConfig('show_date', 'static_on_main', 'modules', 'boolean');
        if ($saved_id > 0) {
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE id = ?");
            $stmt->bindParam(1, $saved_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($result = $stmt->fetch()) {
                $com_theme = $template->tplget("page", "components/static/");
                $page->setContentPosition('body', $template->assign(array('title', 'text', 'date'), array($result['title'], $result['text'], $show_date ? $result['date'] : null), $com_theme));
            }
        }
    }

    public function after()
    {
    }
}

?>