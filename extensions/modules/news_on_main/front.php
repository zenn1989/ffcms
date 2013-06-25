<?php

class mod_news_on_main_front implements mod_front
{
    public function before()
    {
        global $page,$database,$constant,$extension,$template,$system,$user,$hook;
        $short_theme = $template->tplget('view_short_news', 'components/news/');
        $time = time();
        $page_news_count = $extension->getConfig('count_news_page', 'news', 'components', 'int');
        $max_preview_length = $extension->getConfig('short_news_length', 'news', 'components', 'int');
        $stmt = null;
        $content = null;
        if($extension->getConfig('delay_news_public', 'news', 'components', 'boolean'))
        {
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.date <= ?
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT 0,?");
            $stmt->bindParam(1, $time, PDO::PARAM_INT);
            $stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
            $stmt->execute();
        }
        else
        {
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.id DESC LIMIT 0,?");
            $stmt->bindParam(1, $page_news_count, PDO::PARAM_INT);
            $stmt->execute();
        }
        while($result = $stmt->fetch())
        {
            $news_short_text = $result['text'];
            if($system->contains('<!-- pagebreak -->', $news_short_text))
            {
                $news_short_text = strstr($news_short_text, '<!-- pagebreak -->', true);
            }
            elseif($system->length($news_short_text) > $max_preview_length)
            {
                $news_short_text = $system->sentenceSub($news_short_text, $max_preview_length)."...";
            }
            if($result['path'] == null)
            {
                $news_full_link = $result['link'];
            }
            else
            {
                $news_full_link = $result['path']."/".$result['link'];
            }
            $hashWay = $page->hashFromPathway($system->altexplode('/', $news_full_link));
            $comment_count = $hook->get('comment')->getCount($hashWay);
            $content .= $template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'news_full_link', 'news_comment_count'),
                array($result['title'], $news_short_text, $system->toDate($result['date'], 'h'), $result['path'], $result['name'], $result['author'], $user->get('nick', $result['author']), $news_full_link, $comment_count),
                $short_theme);
        }
        if($content != null)
        {
            $content .= $template->drowNumericPagination(0, $page_news_count, $this->totalNews(), "news/");
        }
        $page->setContentPosition('body', $content);
    }

    private function totalNews()
    {
        global $database,$constant;
        $time = time();
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE display = 1 AND date <= ?");
        $stmt->bindParam(1, $time, PDO::PARAM_INT);
        $stmt->execute();
        $re = $stmt->fetch();
        return $re[0];
    }

    public function after() {}
}

?>