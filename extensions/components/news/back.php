<?php 
class com_news_back
{
	private $list_count = 10;
	
	public function load()
	{
		global $admin,$template,$language,$database,$constant,$system,$user;
		$action_page_title = $admin->getExtName()." : ";
		$work_body = null;
		$menu_theme = $template->tplget('config_menu', null, true);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=list', $language->get('admin_component_news_manage')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=add', $language->get('admin_component_news_add')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=category', $language->get('admin_component_news_category')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=settings', $language->get('admin_component_news_settings')), $menu_theme);
		if($admin->getAction() == "list" || $admin->getAction() == null)
		{
			$action_page_title .= $language->get('admin_component_news_manage');
			$theme_list = $template->tplget('news_list', 'components/', true);
			$index_start = $admin->getPage();
			$news_array = array();
			$stmt = $database->con()->prepare("SELECT a.id,a.title,a.category,a.link,b.category_id,b.path FROM {$constant->db['prefix']}_com_news_entery a, {$constant->db['prefix']}_com_news_category b WHERE a.category = b.category_id ORDER BY a.id DESC LIMIT ?, ?");
			$stmt->bindParam(1, $index_start, PDO::PARAM_INT);
			$stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
			$stmt->execute();
			while($result = $stmt->fetch())
			{
				$news_id = $result['id'];
				$editable_name = "<a href=\"?object=components&id={$admin->getID()}&action=edit&page={$news_id}\">{$result['title']}</a>";
				$full_link = "<a href=\"{$constant->url}/news/{$result['path']}/{$result['link']}\" target=\"_blank\">{$result['path']}/{$result['link']}</a>";
				$news_array[] = array($news_id, $editable_name, $full_link, $result['category']);
			}
			$form_table = $admin->tplRawTable(array($language->get('admin_component_news_th_id'), $language->get('admin_component_news_th_title'), $language->get('admin_component_news_th_link'), $language->get('admin_component_news_th_manage')), $news_array);
			$work_body = $template->assign(array('ext_table_data'), array($form_table), $theme_list);
		}
		elseif($admin->getAction() == "edit" && $this->newsExist())
		{
			$news_id = $admin->getPage();
			$action_page_title .= $language->get('admin_component_news_modedit_title');
			$notify = null;
			if($system->post('save'))
			{
				$editor_id = $user->get('id');
				$title = $system->nohtml($system->post('title'));
				$category_id = $system->post('category');
				$pathway = $system->nohtml($system->post('pathway')).".html";
				$display = $system->post('display_content') == "on" ? 1 : 0;
				$important = $system->post('important_content') == "on" ? 1 : 0;
				$text = $system->post('text');
				$description = $system->nohtml($system->post('description'));
				$keywords = $system->nohtml($system->post('keywords'));
				$date = $system->post('current_date') == "on" ? time() : $system->toUnixTime($system->post('date'));
				if(strlen($title) < 1)
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_title_length'));
				}
				if(!$system->isInt($category_id))
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_category_wrong'));
				}
				if(strlen($pathway) < 1 || !$this->check_pageway($pathway, $news_id, $category_id))
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_pathway_null'));
				}
				if(strlen($text) < 1)
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_text_null'));
				}
				if($notify == null)
				{
						$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_news_entery SET 
						title = ?, 
						text = ?, 
						link = ?, 
						category = ?, 
						date = ?, 
						author = ?, 
						description = ?, 
						keywords = ?, 
						display = ?, 
						important = ? 
						WHERE id = ?");
						$stmt->bindParam(1, $title, PDO::PARAM_STR);
						$stmt->bindParam(2, $text, PDO::PARAM_STR);
						$stmt->bindParam(3, $pathway, PDO::PARAM_STR);
						$stmt->bindParam(4, $category_id, PDO::PARAM_INT);
						$stmt->bindParam(5, $date, PDO::PARAM_INT);
						$stmt->bindParam(6, $editor_id, PDO::PARAM_INT);
						$stmt->bindParam(7, $description, PDO::PARAM_STR);
						$stmt->bindParam(8, $keywords, PDO::PARAM_STR);
						$stmt->bindParam(9, $display, PDO::PARAM_INT);
						$stmt->bindParam(10, $important, PDO::PARAM_INT);
						$stmt->bindParam(11, $news_id, PDO::PARAM_INT);
						$stmt->execute();
						$notify .= $template->stringNotify('success', $language->get('admin_component_news_edit_notify_success_save'));
				}
			}
			$edit_theme = $template->tplget('news_edit', 'components/', true);
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_entery WHERE id = ?");
			$stmt->bindParam(1, $news_id, PDO::PARAM_INT);
			$stmt->execute();
			$news_result = $stmt->fetch();
			$category_option_list = $this->buildCategoryOptionList($news_id);
			$is_display = $news_result['display'] > 0 ? "checked" : null;
			$is_important = $news_result['important'] > 0 ? "checked" : null;
			
			$work_body = $template->assign(array('news_title', 'news_path', 'news_content', 'news_description', 'news_keywords', 'news_date', 'category_option_list', 'notify_message', 'news_display_check', 'news_important_check'), 
					array($news_result['title'], $system->noextention($news_result['link']), $news_result['text'], $news_result['description'], $news_result['keywords'], $system->toDate($news_result['date'], 'h'), $category_option_list, $notify, $is_display, $is_important), 
					$edit_theme);
			$stmt = null;
		}
		elseif($admin->getAction() == "add")
		{
			$action_page_title .= $language->get('admin_component_news_add');
			$theme = $template->tplget('news_edit', 'components/', true);
			$notify = null;
			if($system->post('save'))
			{
				$editor_id = $user->get('id');
				$title = $system->nohtml($system->post('title'));
				$category_id = $system->post('category');
				$pathway = $system->nohtml($system->post('pathway')).".html";
				$display = $system->post('display_content') == "on" ? 1 : 0;
				$important = $system->post('important_content') == "on" ? 1 : 0;
				$text = $system->post('text');
				$description = $system->nohtml($system->post('description'));
				$keywords = $system->nohtml($system->post('keywords'));
				$date = $system->post('current_date') == "on" ? time() : $system->toUnixTime($system->post('date'));
				if(strlen($title) < 1)
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_title_length'));
				}
				if(!$system->isInt($category_id))
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_category_wrong'));
				}
				if(strlen($pathway) < 1 || !$this->check_pageway($pathway, $news_id, $category_id))
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_pathway_null'));
				}
				if(strlen($text) < 1)
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_news_edit_notify_text_null'));
				}
				if($notify == null)
				{
					$owner_id = $user->get('id');
					$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_news_entery 
					(`title`, `text`, `link`, `category`, `date`, `author`, `description`, `keywords`, `display`, `important`) VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$stmt->bindParam(1, $title, PDO::PARAM_STR);
					$stmt->bindParam(2, $text, PDO::PARAM_STR);
					$stmt->bindParam(3, $pathway, PDO::PARAM_STR);
					$stmt->bindParam(4, $category_id, PDO::PARAM_INT);
					$stmt->bindParam(5, $date, PDO::PARAM_STR);
					$stmt->bindParam(6, $owner_id, PDO::PARAM_INT);
					$stmt->bindParam(7, $description, PDO::PARAM_STR);
					$stmt->bindParam(8, $keywords, PDO::PARAM_STR);
					$stmt->bindParam(9, $display, PDO::PARAM_INT);
					$stmt->bindParam(10, $important, PDO::PARAM_INT);
					$stmt->execute();
					$system->redirect($_SERVER['PHP_SELF']."?object=components&id=".$admin->getID());
					return;
				}
				else
				{
					$work_body = $template->assign(array('category_option_list', 'notify_message', 'news_title', 'news_path', 'news_content', 'news_description', 'news_keywords', 'news_date'),
							array($this->buildCategoryOptionList(), $notify, $title, $system->noextention($pathway), $text, $description, $keywords, $system->toDate($date, 'h')),
							$theme);
				}
			}
			else
			{
				$work_body = $template->assign('category_option_list', $this->buildCategoryOptionList(), $theme);
			}
		}
		elseif($admin->getAction() == "settings")
		{
			$action_page_title .= $language->get('admin_component_news_settings');
			
			if($system->post('submit'))
			{
				$save_try = $admin->trySaveConfigs();
				if($save_try)
					$work_body .= $template->stringNotify('success', $language->get('admin_extension_config_update_success'), true);
				else
					$work_body .= $template->stringNotify('error', $language->get('admin_extension_config_update_fail'), true);;
			}
			
			$config_form = $template->tplget('config_form', null, true);
			
			$config_set .= $language->get('admin_component_news_description');
			$config_set .= $admin->tplSettingsDirectory($language->get('admin_component_news_settings_mainblock'));
			$config_set .= $admin->tplSettingsSelectYorN('config:delay_news_public', $language->get('admin_component_news_config_newsdelay_title'), $language->get('admin_component_news_config_newsdelay_desc'), $admin->getConfig('delay_news_public', 'boolean'));
			$config_set .= $admin->tplSettingsInputText('config:count_news_page', $admin->getConfig('count_news_page', 'int'), $language->get('admin_component_news_config_newscount_page_title'), $language->get('admin_component_news_config_newscount_page_desc'));
			$config_set .= $admin->tplSettingsInputText('config:short_news_length', $admin->getConfig('short_news_length', 'int'), $language->get('admin_component_news_config_newsshort_length_title'), $language->get('admin_component_news_config_newsshort_length_desc'));
			$config_set .= $admin->tplSettingsDirectory($language->get('admin_component_news_settings_catblock'));
			$config_set .= $admin->tplSettingsSelectYorN('config:multi_category', $language->get('admin_component_news_config_newscat_multi_title'), $language->get('admin_component_news_config_newscat_multi_desc'), $admin->getConfig('multi_category', 'boolean'));
			
			$work_body .= $template->assign('ext_form', $config_set, $config_form);
		}
		
		$body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
		return $body_form;
	}
	
	private function newsExist()
	{
		global $database,$constant,$admin,$system;
		$newsId = $admin->getPage();
		if($system->isInt($newsId))
		{
			$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE id = ?");
			$stmt->bindParam(1, $newsId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch();
			return $result > 0 ? true : false;
		}
		return false;
	}
	
	private function buildCategoryOptionList($news_id = 0)
	{
		global $database,$constant,$system,$template;
		$theme_option_active = $template->tplget('form_option_item_active', null, true);
		$theme_option_inactive = $template->tplget('form_option_item_inactive', null, true);
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_news_category ORDER BY `path` ASC");
		$stmt->execute();
		$result_array = array();
		$result_id = array();
		$result_name = array();
		$result_string = null;
		while($result = $stmt->fetch())
		{
			$result_array[] = $result['path'];
			$result_id[$result['path']] = $result['category_id'];
			$result_name[$result['path']] = $result['name'];
		}
		sort($result_array);
		$cstmt = $database->con()->prepare("SELECT category FROM {$constant->db['prefix']}_com_news_entery WHERE id = ?");
		$cstmt->bindParam(1, $news_id, PDO::PARAM_INT);
		$cstmt->execute();
		$cRes = $cstmt->fetch();
		$news_category_id = $cRes['category'];
		$cstmt = null;
		foreach($result_array as $path)
		{
			$spliter_count = substr_count($path, "/");
			$add = '';
			if($path != null)
			{
				for($i=-1;$i<=$spliter_count;$i++)
				{
					$add .= "-";
				}
			}
			else
			{
				$add = "-";
			}
			$current_id = $result_id[$path];
			$current_name = $result_name[$path];
			if($current_id == $news_category_id)
			{
				$result_string .= $template->assign(array('option_value', 'option_name'), array($current_id, $add." ".$current_name), $theme_option_active);
			}
			else
			{
				$result_string .= $template->assign(array('option_value', 'option_name'), array($current_id, $add." ".$current_name), $theme_option_inactive);
			}
		}
		$stmt = null;
		return $result_string;
	}
	
	private function check_pageway($way, $id = 0, $cat_id)
	{
		global $database,$constant;
		if(preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way))
		{
			return false;
		}
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_news_entery WHERE link = ? AND category = ? AND id != ?");
		$stmt->bindParam(1, $way, PDO::PARAM_STR);
		$stmt->bindParam(2, $cat_id, PDO::PARAM_INT);
		$stmt->bindParam(3, $id, PDO::PARAM_INT);
		$stmt->execute();
		$pRes = $stmt->fetch();
		return $pRes[0] == 0 ? true : false;
	}
}

?>