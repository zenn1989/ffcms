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
		// /cat/cat2/url.html
		if(sizeof($categories) >= 1)
		{
			$link_cat = $system->altimplode("/", $categories);
			$catstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path = ?");
			$catstmt->bindParam(1, $link_cat, PDO::PARAM_STR);
			$catstmt->execute();
			if($catresult = $catstmt->fetch())
			{
				$category_link = $catresult['path'];
				$category_text = $catresult['name'];
				$rule->getInstance()->add('com.news.have_category', true);
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery WHERE link = ? AND category = ?");
				$stmt->bindParam(1, $url, PDO::PARAM_STR);
				$stmt->bindParam(2, $catresult['category_id'], PDO::PARAM_INT);
				$stmt->execute();
			}
		}
		// /url.html
		else
		{
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery WHERE link = ? AND category = 0");
			$stmt->bindParam(1, $url, PDO::PARAM_STR);
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
		global $page,$system,$database,$constant,$template,$user,$rule;
		$cat_link = $system->altimplode("/", $page->shiftPathway());
		// TODO: добавить выборку по like %cat_link при условии не вхождения в null
		$cstmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category WHERE path = ?");
		$cstmt->bindParam(1, $cat_link, PDO::PARAM_STR);
		$cstmt->execute();
		$content = null;
		$max_preview_length = 200;
		if($cat_result = $cstmt->fetch())
		{
			$rule->getInstance()->add('com.news.have_category', true);
			$short_theme = $template->tplget('view_short_news', 'components/news/');
			$cat_id = $cat_result['category_id'];
			$time = time();
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery WHERE category = ? AND date <= ? ORDER BY date DESC");
			$stmt->bindParam(1, $cat_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $time, PDO::PARAM_INT);
			$stmt->execute();
			while($result = $stmt->fetch())
			{
				$news_short_text = $result['text'];
				if($system->length($news_short_text) > $max_preview_length)
				{
					$news_short_text = $system->sentenceSub($news_short_text, $max_preview_length)."...";
				}
				$content .= $template->assign(array('news_title', 'news_text', 'news_date', 'news_category_url', 'news_category_text', 'author_id', 'author_nick', 'news_self_url'), 
					array($result['title'], $news_short_text, $system->toDate($result['date'], 'h'), $cat_result['path'], $cat_result['name'], $result['author'], $user->get('nick', $result['author']), $result['link']), 
					$short_theme);
			}
			$stmt = null;
		}
		
		$cstmt = null;
		return $content;
	}
}

?>