<?php

class mod_tagcloud_front implements mod_front {
    public function after() {}

    public function before() {
        global $engine;
        // res: tag => count repeat
        $theme_head = $engine->template->get('block_head', 'modules/mod_tagcloud/');
        $theme_body = $engine->template->get('block_body', 'modules/mod_tagcloud/');
        $theme_empty = $engine->template->get('block_empty', 'modules/mod_tagcloud/');
        $tag_count = $engine->extension->getConfig('tag_count', 'tagcloud', 'modules', 'int');
        if($tag_count < 1)
            $tag_count = 1;
        $stmt = $engine->database->con()->prepare("SELECT SQL_CALC_FOUND_ROWS tag, COUNT(*) AS count FROM {$engine->constant->db['prefix']}_mod_tags WHERE object_type = 'news' GROUP BY tag ORDER BY count DESC LIMIT 0,?");
        $stmt->bindParam(1, $tag_count, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $content = null;
        foreach($result as $tag) {
            $content .= $engine->template->assign(array('mod_tagcloud_name', 'mod_tagcloud_count'), array($tag['tag'], $tag['count']), $theme_body);
            $content .= " ";
        }
        $full_content = $engine->template->assign('mod_tagcloud_list', $content == null ? $theme_empty : $content, $theme_head);
        $engine->page->setContentPosition($engine->extension->getConfig('template_position_name', 'tagcloud', 'modules'), $full_content, $engine->extension->getConfig('template_position_index', 'tagcloud', 'modules', 'int'));
    }
}



?>