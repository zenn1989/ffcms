<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class mod_comments_front implements mod_front
{
    public function before()
    {
    }

    public function after()
    {
        global $engine;
        if (!$engine->page->isMain() && $engine->template->tagRepeatCount('com.comment_list') == 1 && $engine->template->tagRepeatCount('com.comment_form') == 1 && !$engine->page->isNullPage()) {
            $engine->template->globalSet('com.comment_list', $this->buildComments());
            if ($engine->user->get('id') > 0)
                $engine->template->globalSet('com.comment_form', $this->buildFormAdd());
            else
                $engine->template->globalSet('com.comment_form', $engine->template->stringNotify('warning', $engine->language->get('comments_register_msg')));
        }
        return;
    }

    private function buildComments()
    {
        global $engine;
        $userid = $engine->user->get('id');
        $theme_list = $engine->template->get('comment_list', 'modules/mod_comments/');
        $comment_count = $engine->extension->getConfig('comments_count', 'comments', 'modules', 'int');
        $content = null;
        $hash = $engine->page->hashFromPathway();
        $way = $engine->page->getPathway();
        $object = $way[0];
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_mod_comments WHERE target_hash = ? AND object_name = ? ORDER BY id DESC LIMIT 0,?");
        $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
        $stmt->bindParam(2, $object, PDO::PARAM_STR);
        $stmt->bindParam(3, $comment_count, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $engine->user->listload($engine->system->extractFromMultyArray('author', $result));
        foreach ($result as $item) {
            $edit_link = null;
            $delete_link = null;
            $poster_id = $item['author'];
            $editconfig = $engine->extension->getConfig('edit_time', 'comments', 'modules', 'int');
            if ($userid > 0) {
                if (($poster_id == $userid && (time() - $item['time']) <= $editconfig) || $engine->user->get('mod_comment_edit') > 0) {
                    $edit_link = $engine->template->assign('comment_id', $item['id'], $engine->template->get('comment_link_edit', 'modules/mod_comments/'));
                }
                if ($engine->user->get('mod_comment_delete') > 0) {
                    $delete_link = $engine->template->assign('comment_id', $item['id'], $engine->template->get('comment_link_delete', 'modules/mod_comments/'));
                }
            }
            $content .= $engine->template->assign(array('poster_id', 'poster_nick', 'poster_avatar', 'comment_text', 'comment_date', 'comment_id', 'comment_link_edit', 'comment_link_delete'),
                array($poster_id, $engine->user->get('nick', $poster_id), $engine->user->buildAvatar('small', $poster_id), $engine->hook->get('bbtohtml')->bbcode2html($item['comment']), $engine->system->toDate($item['time'], 'h'), $item['id'], $edit_link, $delete_link),
                $theme_list);
        }
        $stmt = null;
        return $content;
    }

    private function buildFormAdd()
    {
        global $engine;
        return $engine->template->get('comment_form', 'modules/mod_comments/');
    }
}


?>