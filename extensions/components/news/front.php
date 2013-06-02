<?php

if(!extension::registerPathWay('news', 'news')) {
	exit("Component static cannot be registered!");
}

class com_news_front implements com_front
{
	public function load()
	{
		global $page,$system,$template;
		$content = null;
		$way = $page->shiftPathway();
		// ищем последний элемент
		$last_object = array_pop($way);
		// на всякий сохраняем массив категорий
		$category_array = $way;
		// это одиночная статлья
		if($system->suffixEquals($last_object, '.html'))
		{
			$content = $this->viewFullNews($last_object, $category_array);	
		}
		// иначе это содержимое категории
		else
		{
			$content = $this->viewCategory();
		}
		if($content == null)
			$content = $template->compile404();
		$page->setContentPosition('body', $content);
	}
	
	public function viewFullNews($url, $categories)
	{
		global $database,$constant,$system,$template,$rule,$user;
		$stmt = null;
		$category_link = null;
		$category_text = null;
		$link_cat = $system->altimplode("/", $categories);
		$time = time();
		if($link_cat != null)
		{
			$rule->getInstance()->add('com.news.have_category', true);
		}
		else
		{
			$rule->getInstance()->add('com.news.have_category', false);
		}
		$catstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path = ?");
		$catstmt->bindParam(1, $link_cat, PDO::PARAM_STR);
		$catstmt->execute();
		if($catresult = $catstmt->fetch())
		{
			$category_link = $catresult['path'];
			$category_text = $catresult['name'];
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery WHERE link = ? AND category = ? AND display = 1 AND date <= ?");
			$stmt->bindParam(1, $url, PDO::PARAM_STR);
			$stmt->bindParam(2, $catresult['category_id'], PDO::PARAM_INT);
			$stmt->bindParam(3, $time, PDO::PARAM_INT);
			$stmt->execute();
		}
		if($stmt != null && $result = $stmt->fetch())
		{
			$news_theme = $template->tplget('view_full_news', 'components/news/');
			return $template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick'), 
					array($result['title'], $result['text'], $system->toDate($result['date'], 'h'), $category_link, $category_text, $result['author'], $user->get('nick', $result['author'])), 
					$news_theme);
		}
		return null;
	}
	
	public function viewCategory()
	{
		global $page,$system,$database,$constant,$template,$user,$rule,$extension;
		$way = $page->shiftPathway();
		$content = null;
		$pop_array = $way;
		$last_item = array_pop($pop_array);
		$page_index = 0;
		$page_news_count = $extension->getConfig('count_news_page', 'news', 'components', 'int');
		$total_news_count = 0;
		$cat_link = null;
		if($system->isInt($last_item))
		{
			$page_index = $last_item;
			$cat_link = $system->altimplode("/", $pop_array);
		}
		else
		{
			$cat_link = $system->altimplode("/", $way);
		}
		$select_coursor_start = $page_index * $page_news_count;
		
		$category_select_array = array();
		$category_list = null;
		$fstmt = null;
		if($extension->getConfig('multi_category', 'news', 'components', 'boolean'))
		{
			$fstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path like ?");
			$path_swarm = "$cat_link%";
			$fstmt->bindParam(1, $path_swarm, PDO::PARAM_STR);
			$fstmt->execute();
		}
		else
		{
			$fstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path = ?");
			$fstmt->bindParam(1, $cat_link, PDO::PARAM_STR);
			$fstmt->execute();
		}
		while($fresult = $fstmt->fetch())
		{
			$category_select_array[] = $fresult['category_id'];
		}
		$category_list = $system->altimplode(',', $category_select_array);
		$fstmt = null;
		if($system->isIntList($category_list))
		{
			$short_theme = $template->tplget('view_short_news', 'components/news/');
			$max_preview_length = $extension->getConfig('short_news_length', 'news', 'components', 'int');
			$time = time();
			$stmt = null;
			$cstmt = null;
			if($extension->getConfig('delay_news_public', 'news', 'components', 'boolean'))
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a, 
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.category in ($category_list) AND a.date <= ? 
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.date DESC LIMIT ?,?");
				$stmt->bindParam(1, $time, PDO::PARAM_INT);
				$stmt->bindParam(2, $select_coursor_start, PDO::PARAM_INT);
				$stmt->bindParam(3, $page_news_count, PDO::PARAM_INT);
				$stmt->execute();
				$cstmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE category in ($category_list) AND date <= ?");
				$cstmt->bindParam(1, $time, PDO::PARAM_INT);
				$cstmt->execute();
				if($countRows = $cstmt->fetch())
				{
					$total_news_count = $countRows[0];
				}
				$cstmt = null;
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery a,
												  {$constant->db['prefix']}_com_news_category b
												  WHERE a.category in ($category_list)
												  AND a.category = b.category_id
												  AND a.display = 1
												  ORDER BY a.important DESC, a.id DESC LIMIT ?,?");
				$stmt->bindParam(1, $select_coursor_start, PDO::PARAM_INT);
				$stmt->bindParam(2, $page_news_count, PDO::PARAM_INT);
				$stmt->execute();
				
				$cstmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE category in ($category_list)");
				$cstmt->execute();
				if($countRows = $cstmt->fetch())
				{
				$total_news_count = $countRows[0];
				}
				$cstmt = null;
			}
			if(sizeof($category_select_array) > 0)
			{
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
					$content .= $template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'news_full_link'),
							array($result['title'], $news_short_text, $system->toDate($result['date'], 'h'), $result['path'], $result['name'], $result['author'], $user->get('nick', $result['author']), $news_full_link),
							$short_theme);
				}
			}
			$stmt = null;
		}
		$cstmt = null;
		if($content != null)
		{
			$category_theme = $template->tplget('view_category', 'components/news/');
            $page_link = $cat_link == null ? "news/" : "news/".$cat_link."/";
			$content = $template->assign(array('news_body', 'pagination'), array($content, $template->drowNumericPagination($page_index, $page_news_count, $total_news_count, $page_link)), $category_theme);
		}
		return $content;
	}
}

?>