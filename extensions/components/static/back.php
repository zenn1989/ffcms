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
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static WHERE title like ? OR text like ? LIMIT ?,?");
				$stmt->bindParam(1, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(2, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(3, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(4, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_com_static LIMIT ?,?");
				$stmt->bindParam(1, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			$thead_theme = $template->tplget('static_list_thead', 'components/', true);
			$tbody_theme = $template->tplget('static_list_tbody', 'components/', true);
			$tbody = null;
			while($res = $stmt->fetch())
			{
				$edit_link = "?object=components&id=".$admin->getID()."&action=edit&page=".$res['id'];
				$delete_link = "?object=components&id=".$admin->getID()."&action=delete&page=".$res['id'];
				$tbody .= $template->assign(array('page_id', 'page_title', 'page_path', 'page_edit', 'com_pathway', 'page_delete'),
						array($res['id'], $res['title'], $res['pathway'], $edit_link, $this->com_pathway, $delete_link),
						$tbody_theme);
			}
			$pagination_list_theme = $template->tplget('static_list_pagination', 'components/', true);
			$pagination_page_count = $this->getPageCount();
			$ret_position = intval($admin->getPage()/$this->list_count);
			$pagination_list = null;
			if($pagination_page_count <= 10 || $ret_position < 5)
			{
				for($i=0;$i<=$pagination_page_count;$i++)
				{
					$link_page = $i*$this->list_count;
					$pagination_list .= $template->assign(
							array('ext_pagination_href', 'ext_pagination_index'), 
							array('?object=components&id='.$admin->getID().'&action=list&page='.$link_page, $i), 
							$pagination_list_theme);
				}
			}
			else
			{
					// Наркомания дикая, но работает
					// алгоритм: ----1 ---2 --3 -4 <ret_position> +6 ++7 +++8 ++++9
					$start_ret = $ret_position-4;
					$end_ret = $ret_position+4;
					// -3>0
					for(;$start_ret<$ret_position;$start_ret++)
					{
						$pagination_list .= $template->assign(
							array('ext_pagination_href', 'ext_pagination_index'), 
							array('?object=components&id='.$admin->getID().'&action=list&page='.$start_ret*$this->list_count, $start_ret), 
							$pagination_list_theme);
					}
					// 0
					$pagination_list .= $template->assign(
							array('ext_pagination_href', 'ext_pagination_index'),
							array('?object=components&id='.$admin->getID().'&action=list&page='.$ret_position*$this->list_count, $ret_position),
							$pagination_list_theme);
					$ret_position++;
					// 0>+3
					for(;$ret_position<=$end_ret;$ret_position++)
					{
						if($ret_position <= $pagination_page_count)
						{
							$pagination_list .= $template->assign(
									array('ext_pagination_href', 'ext_pagination_index'),
									array('?object=components&id='.$admin->getID().'&action=list&page='.$ret_position*$this->list_count, $ret_position),
									$pagination_list_theme);
						}
					}
				
			}
			$work_body = $template->assign(array('ext_body_result', 'ext_search_value', 'ext_pagination_list'), array($tbody, $system->post('search'), $pagination_list), $thead_theme);
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
		if(preg_match('/[\'\/~`\!@#\$%\^&\*\(\)+=\{\}\[\]\|;:"\<\>,\?\\\]/', $way))
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