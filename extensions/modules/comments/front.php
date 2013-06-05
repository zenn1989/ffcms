<?php

class mod_comments_front implements mod_front
{
    public function before() {}

    public function after()
    {
        global $page,$template;
        if(!$page->isMain() && $template->tagRepeatCount('com.comment_list') == 1 && $template->tagRepeatCount('com.comment_form') == 1 && !$page->isNullPage())
        {
            $template->globalSet('com.comment_list', $this->buildComments());
            $template->globalSet('com.comment_form', $this->buildFormAdd());
        }
        return;
    }

    private function buildComments()
    {
        global $template,$database,$constant,$page,$user,$system,$extension,$hook;
        $theme_list = $template->tplget('comment_list', 'modules/mod_comments/');
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
        foreach($result as $item)
        {
            $poster_id = $item['author'];
            $content .= $template->assign(array('poster_id', 'poster_nick', 'poster_avatar', 'comment_text', 'comment_date'),
                array($poster_id, $user->get('nick', $poster_id), $user->buildAvatar('small', $poster_id), $hook->get('bbtohtml')->bbcode2html($item['comment']), $system->toDate($item['time'], 'h')),
                $theme_list);
        }
        $stmt = null;
        return $content;
    }

    private function buildFormAdd()
    {
        global $template,$page;
        return $template->tplget('comment_form', 'modules/mod_comments/');
    }
}



?>