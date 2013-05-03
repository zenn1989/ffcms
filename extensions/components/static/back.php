<?php
class com_static_back
{
	private $list_count = 10;
	private $com_pathway = "static";

	public function load()
	{
		global $template,$admin,$language,$database,$constant,$system,$user;
		$config_pharse = null;
		$work_body = null;
		$action_page_title = $admin->getExtName()." : ";
		$stmt = null;
		if($admin->getAction() == "list" || $admin->getAction() == NULL)
		{
			$action_page_title .= $language->get('admin_component_static_control');
			$index_start = $admin->getPage();
			if($system->post('dosearch') && strlen($system->post('search')) > 0)
			{
				$search_string = "%{$system->post('search')}%";
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE title like ? OR text like ? ORDER BY id DESC LIMIT ?,?");
				$stmt->bindParam(1, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(2, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(3, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(4, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static ORDER BY id DESC LIMIT ?,?");
				$stmt->bindParam(1, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			$static_theme = $template->tplget('static_list', 'components/', true);
			$static_manage = $template->tplget('static_list_manage', 'components/', true);
			$tbody = null;
			$static_array_data = array();
			while($res = $stmt->fetch())
			{
				$edit_link = "?object=components&id=".$admin->getID()."&action=edit&page=".$res['id'];
				$delete_link = "?object=components&id=".$admin->getID()."&action=delete&page=".$res['id'];
				$manage_link = $template->assign(array('page_edit', 'page_delete'), array($edit_link, $delete_link), $static_manage);
				$title_with_edit = '<a href="'.$edit_link.'">'.$res['title'].'</a>';
				$path_with_view = '<a href="'.$constant->url.'/'.$this->com_pathway.'/'.$res['pathway'].'" target="_blank">/'.$this->com_pathway.'/'.$res['pathway'].'</a>';
				$static_array_data[] = array($res['id'], $title_with_edit, $path_with_view, $manage_link);
			}
			$tbody =  $admin->tplrawTable(array($language->get('admin_component_static_th_id'), $language->get('admin_component_static_th_title'), $language->get('admin_component_static_th_path'), $language->get('admin_component_static_th_edit')), $static_array_data);
			$pagination_list = $admin->tplRawPagination($this->list_count, $this->getPageCount(), 'components');
			$work_body = $template->assign(array('ext_table', 'ext_search_value', 'ext_pagination_list'), array($tbody, $system->post('search'), $pagination_list), $static_theme);
		}
		elseif($admin->getAction() == "edit")
		{
			$notify = null;
			if($system->post('save'))
			{

				$page_id = $admin->getPage();
				$page_title = $system->nohtml($system->post('title'));
				$page_way = $system->nohtml($system->post('pathway').".html");
				$page_text = $system->post('text');
				$page_description = $system->nohtml($system->post('description'));
				$page_keywords = $system->nohtml($system->post('keywords'));
				$page_date = $system->nohtml($system->post('date'));
				if($this->check_pageway($page_way, $page_id))
				{
					$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_com_static SET title = ?, text = ?, pathway = ?, description = ?, keywords = ?, date = ? WHERE id = ?");
					$stmt->bindParam(1, $page_title, PDO::PARAM_STR);
					$stmt->bindParam(2, $page_text, PDO::PARAM_STR);
					$stmt->bindParam(3, $page_way, PDO::PARAM_STR);
					$stmt->bindParam(4, $page_description, PDO::PARAM_STR);
					$stmt->bindParam(5, $page_keywords, PDO::PARAM_STR);
					$stmt->bindParam(6, $page_date, PDO::PARAM_STR);
					$stmt->bindParam(7, $page_id, PDO::PARAM_INT);
					$stmt->execute();
					$stmt = null;
					$notify = $template->compileNotify('success', $language->get('admin_component_static_page_saved'), true);
				}
				else
				{
					$notify = $template->compileNotify('error', $language->get('admin_component_static_page_notsaved'), true);
				}
			}
			$action_page_title .= $language->get('admin_component_static_edit');
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE id = ?");
			$page_id = $admin->getPage();
			$stmt->bindParam(1, $page_id, PDO::PARAM_INT);
			$stmt->execute();
			if($stmt->rowCount() != 1)
			{
				$work_body = "<p>Not found!</p>";
			}
			else
			{
				$result = $stmt->fetch();
				$way = $system->noextention($result['pathway']);
				$work_body = $template->assign(array('static_title', 'static_text', 'static_path', 'static_description', 'static_keywords', 'static_date', 'notify_message'),
						array($result['title'], $result['text'], $way, $result['description'], $result['keywords'], $result['date'], $notify),
						$template->tplget('static_edit', 'components/', true));
			}
		}
		elseif($admin->getAction() == "add")
		{
			$this->check_pageway('text.html');
			$action_page_title .= $language->get('admin_component_static_add');
			if($system->post('save'))
			{
				$page_id = $admin->getPage();
				$page_title = $system->nohtml($system->post('title'));
				$page_way = $system->nohtml($system->post('pathway').".html");
				$page_text = $system->post('text');
				$page_description = $system->nohtml($system->post('description'));
				$page_keywords = $system->nohtml($system->post('keywords'));
				$page_date = $system->nohtml($system->post('date'));
				$page_owner = $user->get('id');
				if($this->check_pageway($page_way))
				{
					$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_static (title, text, owner, pathway, date, description, keywords) VALUES (?, ?, ?, ?, ?, ?, ?)");
					$stmt->bindParam(1, $page_title, PDO::PARAM_STR);
					$stmt->bindParam(2, $page_text, PDO::PARAM_STR);
					$stmt->bindParam(3, $page_owner, PDO::PARAM_INT);
					$stmt->bindParam(4, $page_way, PDO::PARAM_STR);
					$stmt->bindParam(5, $page_date, PDO::PARAM_STR);
					$stmt->bindParam(6, $page_description, PDO::PARAM_STR);
					$stmt->bindParam(7, $page_keywords, PDO::PARAM_STR);
					$stmt->execute();
					$work_body = $template->assign('static_date', date('Y-m-d'), $template->tplget('static_edit', 'components/', true));
					$system->redirect($_SERVER['PHP_SELF']."?object=components&id=".$admin->getID());
				}
				else
				{
					$notify = $template->compileNotify('error', $language->get('admin_component_static_page_notsaved'), true);
					$work_body = $template->assign(array('static_title', 'static_text', 'static_path', 'static_description', 'static_keywords', 'static_date', 'notify_message'),
							array($page_title, $page_text, $system->noextention($page_way), $page_description, $page_keywords, $page_date, $notify),
							$template->tplget('static_edit', 'components/', true));
				}
			}
			else
			{
				$work_body = $template->assign(array('static_date'),
						array(date('Y-m-d')),
						$template->tplget('static_edit', 'components/', true));
			}
		}
		elseif($admin->getAction() == "delete" && $admin->getPage() > 0)
		{
			$action_page_title .= $language->get('admin_component_static_delete');
			$page_id = $admin->getPage();
			if($system->post('submit'))
			{
				$stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_com_static WHERE id = ?");
				$stmt->bindParam(1, $page_id, PDO::PARAM_INT);
				$stmt->execute();
				$work_body = $language->get('admin_component_static_delete_success_msg');
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE id = ?");
				$stmt->bindParam(1, $page_id, PDO::PARAM_INT);
				$stmt->execute();
				if($stmt->rowCount() > 0)
				{
					$res = $stmt->fetch();
					$array_data[] = array($res['id'], $res['title'], $res['pathway']);
					$tbody =  $admin->tplrawTable(
							array($language->get('admin_component_static_th_id'), $language->get('admin_component_static_th_title'), $language->get('admin_component_static_th_path')),
							$array_data);
						
				}
				$theme_delete = $template->assign(array('static_delete_info', 'cancel_link'),
						array($tbody, '?object=components&id='.$admin->getId()),
						$template->tplget('static_delete', 'components/', true));
				$work_body = $theme_delete;
			}
		}
		$menu_theme = $template->tplget('config_menu', null, true);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=list', $language->get('admin_component_static_control')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=add', $language->get('admin_component_static_add')), $menu_theme);
		$body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
		return $body_form;
	}

	private function getPageCount()
	{
		global $database,$constant;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_com_static");
		$stmt->execute();
		$result = $stmt->fetch();
		return intval($result[0]/$this->list_count);
	}

	private function check_pageway($way, $id = 0)
	{
		global $database,$constant;
		if(preg_match('/[\'~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way))
		{
			return false;
		}
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE pathway = ? AND id != ?");
		$stmt->bindParam(1, $way, PDO::PARAM_STR);
		$stmt->bindParam(2, $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->rowCount() == 0 ? true : false;
	}
}
?>