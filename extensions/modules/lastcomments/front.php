<?php

class mod_lastcomments_front implements mod_front
{
    public function before()
    {
        global $engine;
        $comment_count = $engine->extension->getConfig('last_count', 'lastcomments', 'modules', 'int');
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_mod_comments WHERE `pathway` != '' ORDER BY `time` DESC LIMIT 0,?");
        $stmt->bindParam(1, $comment_count, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        $template_position = $engine->extension->getConfig('template_position_name', 'lastcomments', 'modules');
        $template_index = $engine->extension->getConfig('template_position_index', 'lastcomments', 'modules', 'int');
        if(sizeof($res) > 0) {
            // have comments in db
            $max_comment_char_size = $engine->extension->getConfig('text_length', 'lastcomments', 'modules', 'int');
            $prepared_userlist = $engine->system->extractFromMultyArray('author', $res);
            $engine->user->listload($prepared_userlist);
            $theme_head = $engine->template->get('block_head', 'modules/mod_lastcomments/');
            $theme_body = $engine->template->get('block_body', 'modules/mod_lastcomments/');
            $result_body = null;
            foreach($res as $result) {
                $comment_preview_text = $engine->system->altsubstr($result['comment'], 0, $max_comment_char_size);
                $result_body .= $engine->template->assign(array('comment_user_id', 'comment_user_name', 'comment_object_url', 'comment_text'),
                                array($result['author'], $engine->user->get('nick', $result['author']), $result['pathway'], $comment_preview_text),
                                $theme_body);
            }
            $engine->page->setContentPosition($template_position, $engine->template->assign('mod_comments_list', $result_body, $theme_head), $template_index);
            return;
        }
        // comments not founded
        $engine->page->setContentPosition($template_position, $engine->template->get('block_empty', 'modules/mod_lastcomments/'), $template_index);
    }

    public function after() {}
}



?>