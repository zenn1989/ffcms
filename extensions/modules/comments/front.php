<?php

class mod_comments_front implements mod_front
{
    public function before()
    {
    }

    public function after()
    {
        global $page, $template, $user, $language;
        if (!$page->isMain() && $template->tagRepeatCount('com.comment_list') == 1 && $template->tagRepeatCount('com.comment_form') == 1 && !$page->isNullPage()) {
            $template->globalSet('com.comment_list', $this->buildComments());
            if ($user->get('id') > 0)
                $template->globalSet('com.comment_form', $this->buildFormAdd());
            else
                $template->globalSet('com.comment_form', $template->stringNotify('warning', $language->get('comments_register_msg')));
        }
        return;
    }

    private function buildComments()
    {
        global $template, $database, $constant, $page, $user, $system, $extension, $hook;
        $userid = $user->get('id');
        $theme_list = $template->get('comment_list', 'modules/mod_comments/');
        $comment_count = $extension->getConfig('comments_count', 'comments', 'modules', 'int');
        $content = null;
        $hash = $page->hashFromPathway();
        $way = $page->getPathway();
        $object = $way[0];
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_mod_comments WHERE target_hash = ? AND object_name = ? ORDER BY id DESC LIMIT 0,?");
        $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
        $stmt->bindParam(2, $object, PDO::PARAM_STR);
        $stmt->bindParam(3, $comment_count, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $user->listload($system->extractFromMultyArray('author', $result));
        foreach ($result as $item) {
            $edit_link = null;
            $delete_link = null;
            $poster_id = $item['author'];
            $editconfig = $extension->getConfig('edit_time', 'comments', 'modules', 'int');
            if ($userid > 0) {
                if (($poster_id == $userid && (time() - $item['time']) <= $editconfig) || $user->get('mod_comment_edit') > 0) {
                    $edit_link = $template->assign('comment_id', $item['id'], $template->get('comment_link_edit', 'modules/mod_comments/'));
                }
                if ($user->get('mod_comment_delete') > 0) {
                    $delete_link = $template->assign('comment_id', $item['id'], $template->get('comment_link_delete', 'modules/mod_comments/'));
                }
            }
            $content .= $template->assign(array('poster_id', 'poster_nick', 'poster_avatar', 'comment_text', 'comment_date', 'comment_id', 'comment_link_edit', 'comment_link_delete'),
                array($poster_id, $user->get('nick', $poster_id), $user->buildAvatar('small', $poster_id), $hook->get('bbtohtml')->bbcode2html($item['comment']), $system->toDate($item['time'], 'h'), $item['id'], $edit_link, $delete_link),
                $theme_list);
        }
        $stmt = null;
        return $content;
    }

    private function buildFormAdd()
    {
        global $template;
        return $template->get('comment_form', 'modules/mod_comments/');
    }
}


?>