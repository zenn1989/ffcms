<?php 
class com_usercontrol_back
{
	private $list_count = 10;
	public function load()
	{
		global $admin,$template,$language,$constant,$database,$system,$user;
		$action_page_title = $admin->getExtName()." : ";
		$menu_theme = $template->tplget('config_menu', null, true);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=list', $language->get('admin_component_usercontrol_manage')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=group', $language->get('admin_component_usercontrol_group')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=ban', $language->get('admin_component_usercontrol_serviceban')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=settings', $language->get('admin_component_usercontrol_settings')), $menu_theme);
		$work_body = null;
		if($admin->getAction() == "list" || $admin->getAction() == NULL)
		{
			$action_page_title .= $language->get('admin_component_usercontrol_manage');
			$index_start = $admin->getPage();
			$list_theme = $template->tplget('usercontrol_list', 'components/', true);
			$manage_theme = $template->tplget('usercontrol_list_manage', 'components/', true);
			if($system->post('dosearch') && strlen($system->post('search')) > 0)
			{
				$search_string = "%{$system->post('search')}%";
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user WHERE login like ? OR email like ? OR nick like ? ORDER BY id DESC LIMIT ?,?");
				$stmt->bindParam(1, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(2, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(3, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(4, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(5, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user ORDER BY id DESC LIMIT ?,?");
				$stmt->bindParam(1, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			$user_data_array = array();
			while($res = $stmt->fetch())
			{
				$manage_data = $template->assign(array('ext_id', 'ext_page'), array($admin->getID(), $res['id']), $manage_theme);
				$user_data_array[] = array($res['id'], $res['login'], $res['email'], $manage_data);
			}
			$table_result = $admin->tplrawTable(array($language->get('admin_component_usercontrol_th_id'), $language->get('admin_component_usercontrol_th_login'), $language->get('admin_component_usercontrol_th_email'), $language->get('admin_component_usercontrol_th_edit')), $user_data_array);
			$pagination_list = $admin->tplRawPagination($this->list_count, $this->getPageCount(), 'components');
			$work_body = $template->assign(	array('ext_table_data', 'ext_search_value', 'ext_pagination_list'),	array($table_result, $system->post('search'), $pagination_list), $list_theme);
		}
		elseif($admin->getAction() == "settings")
		{
			if($system->post('submit'))
			{
				$save_try = $admin->trySaveConfigs();
				if($save_try)
					$work_body .= $template->compileNotify('success', $language->get('admin_extension_config_update_success'), true);
				else
					$work_body .= $template->compileNotify('error', $language->get('admin_extension_config_update_fail'), true);;
			}

			$action_page_title .= $language->get('admin_component_usercontrol_settings');
			$config_form = $template->tplget('config_form', null, true);
				
			$config_set .= $language->get('admin_component_usercontrol_description');
			$config_set .= $admin->tplSettingsDirectory($language->get('admin_component_usercontrol_first_data'));
			$config_set .= $admin->tplSettingsSelectYorN('config:login_captcha', $language->get('admin_component_usercontrol_config_logincaptcha_name'), $language->get('admin_component_usercontrol_config_logincaptcha_desc'), $admin->getConfig('login_captcha', 'boolean'));
			$config_set .= $admin->tplSettingsSelectYorN('config:register_captcha', $language->get('admin_component_usercontrol_config_regcaptcha_name'), $language->get('admin_component_usercontrol_config_regcaptcha_desc'), $admin->getConfig('register_captcha', 'boolean'));
			$config_set .= $admin->tplSettingsSelectYorN('config:register_aprove', $language->get('admin_component_usercontrol_config_aprovereg_name'), $language->get('admin_component_usercontrol_config_aprovereg_desc'), $admin->getConfig('register_aprove', 'boolean'));
			$config_set .= $admin->tplSettingsDirectory($language->get('admin_component_usercontrol_second_data'));
			$config_set .= $admin->tplSettingsSelectYorN('config:profile_view', $language->get('admin_component_usercontrol_config_guest_access_name'), $language->get('admin_component_usercontrol_config_guest_access_desc'), $admin->getConfig('profile_view', 'boolean'));
			$config_set .= $admin->tplSettingsInputText('config:wall_post_count', $admin->getConfig('wall_post_count', 'int'), $language->get('admin_component_usercontrol_config_userwall_name'), $language->get('admin_component_usercontrol_config_userwall_desc'));
			$config_set .= $admin->tplSettingsInputText('config:marks_post_count', $admin->getConfig('marks_post_count', 'int'), $language->get('admin_component_usercontrol_config_marks_name'), $language->get('admin_component_usercontrol_config_marks_desc'));
			$config_set .= $admin->tplSettingsInputText('config:friend_page_count', $admin->getConfig('friend_page_count', 'int'), $language->get('admin_component_usercontrol_config_friend_page_count_name'), $language->get('admin_component_usercontrol_config_friend_page_count_desc'));
			$config_set .= $admin->tplSettingsInputText('config:wall_post_delay', $admin->getConfig('wall_post_delay', 'int'), $language->get('admin_component_usercontrol_config_wall_post_delay_name'), $language->get('admin_component_usercontrol_config_wall_post_delay_desc'));
			$config_set .= $admin->tplSettingsInputText('config:pm_count', $admin->getConfig('pm_count', 'int'), $language->get('admin_component_usercontrol_config_pm_count_name'), $language->get('admin_component_usercontrol_config_pm_count_desc'));
			$config_set .= $admin->tplSettingsDirectory($language->get('admin_component_usercontrol_thred_data'));
			$config_set .= $admin->tplSettingsInputText('config:userlist_count', $admin->getConfig('userlist_count', 'int'), $language->get('admin_component_usercontrol_config_userlist_count_name'), $language->get('admin_component_usercontrol_config_userlist_count_desc'));
			
			$work_body .= $template->assign('ext_form', $config_set, $config_form);
				
		}
		elseif($admin->getAction() == "edit")
		{
			$action_page_title .= $language->get('admin_component_usercontrol_edit');
			$object_user_id = $admin->getPage();
			$notify = null;
			if($user->exists($object_user_id))
			{
				if($system->post('submit'))
				{
					$new_nick = $system->post('nick');
					$new_sex = $system->post('sex');
					$new_phone = $system->post('phone');
					$new_webpage = $system->post('webpage');
					$new_birthday = $system->post('birthday');
					$new_status = $system->post('status');
					$new_groupid = $system->post('groupid');
					$new_pass = strlen($system->post('newpass')) > 3 ? $system->doublemd5($system->post('newpass')) : $user->get('pass', $object_user_id);
					$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user a INNER JOIN {$constant->db['prefix']}_user_custom b USING(id) SET a.nick = ?, a.pass = ?, b.birthday = ?, b.sex = ?, b.phone = ?, b.webpage = ?, b.status = ?, a.access_level = ? WHERE a.id = ?");
					$stmt->bindParam(1, $new_nick, PDO::PARAM_STR);
					$stmt->bindParam(2, $new_pass, PDO::PARAM_STR, 32);
					$stmt->bindParam(3, $new_birthday, PDO::PARAM_STR);
					$stmt->bindParam(4, $new_sex, PDO::PARAM_INT);
					$stmt->bindParam(5, $new_phone, PDO::PARAM_STR);
					$stmt->bindParam(6, $new_webpage, PDO::PARAM_STR);
					$stmt->bindParam(7, $new_status, PDO::PARAM_STR);
					$stmt->bindParam(8, $new_groupid, PDO::PARAM_INT);
					$stmt->bindParam(9, $object_user_id, PDO::PARAM_INT);
					$stmt->execute();
					$user->fulluseroverload($object_user_id);
					$notify .= $template->stringNotify('success', $language->get('admin_component_usercontrol_edit_notify_success'), true);
				}
				$theme_option_active = $template->tplget('form_option_item_active', null, true);
				$theme_option_inactive = $template->tplget('form_option_item_inactive', null, true);
				$prepared_option = null;
				$stmt = $database->con()->prepare("SELECT group_id, group_name FROM {$constant->db['prefix']}_user_access_level");
				$stmt->execute();
				$resFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($resFetch as $item_access)
				{
					if($item_access['group_id'] == $user->get('group_id', $object_user_id))
					{
						$prepared_option .= $template->assign(array('option_value', 'option_name'), array($item_access['group_id'], $item_access['group_name']), $theme_option_active);
					}
					else
					{
						$prepared_option .= $template->assign(array('option_value', 'option_name'), array($item_access['group_id'], $item_access['group_name']), $theme_option_inactive);
					}
				}
				$theme_edit = $template->tplget('usercontrol_user_edit', 'components/', true);
				$work_body .= $template->assign(array('target_user_id', 'target_user_login', 'target_user_nick', 'target_user_phone', 'target_user_sex', 'target_user_webpage', 'target_user_birthday', 'target_user_status', 'option_group_prepare', 'notify'), 
						array($object_user_id, $user->get('login', $object_user_id), $user->get('nick', $object_user_id), $user->customget('phone', $object_user_id), $user->customget('sex', $object_user_id), $user->customget('webpage', $object_user_id), $user->customget('birthday', $object_user_id), $user->customget('status', $object_user_id), $prepared_option, $notify), 
						$theme_edit);
			}
		}
		elseif($admin->getAction() == "delete")
		{
			$target_user_id = $admin->getPage();
			$action_page_title .= $language->get('admin_component_usercontrol_delete');
			$target_user_id = $admin->getPage();
			if($system->isInt($target_user_id) && $user->exists($target_user_id))
			{
				$notify = null;
				if($system->post('deleteuser'))
				{
					// защита от дибилов
					if($target_user_id == $system->post('target_user_id'))
					{
						if($user->get('access_level', $target_user_id) == 3)
						{
							$notify .= $template->stringNotify('error', $language->get('admin_component_usercontrol_delete_admin_fail', true));
						}
						else
						{
							// Логика работы PDO в данный момент наступила на грабли и убила его создателя (:
							// выполнить в 1 мультикверь данное невозможно по непонятной причине.
							// Удаление через DELETE [params] FROM table AS hot INNER JOIN table2 AS hot2 ON table.id = table2.id WHERE table.id = ?
							// не приносит результата вовсе при выключенном INNODB (а это не есть базовым тербованием к цмс)
							// поэтому код ниже может вызвать у вас приступ паники или боли в 5ой точке
							// если у вас есть лучший вариант - присылайте на github.
							$stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_user WHERE id = ?");
							$stmt->bindParam(1, $target_user_id, PDO::PARAM_INT);
							$stmt->execute();
							$stmt = null;
							$stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_user_custom WHERE id = ?");
							$stmt->bindParam(1, $target_user_id, PDO::PARAM_INT);
							$stmt->execute();
							$stmt = null;
							$system->redirect(file_name."?object=components&id=".$admin->getID());
							// TODO: удаление из фриендлиста
							exit();
						}
					}
				}
				$theme_delete = $template->tplget('usercontrol_user_delete', 'components/', true);
				$work_body = $template->assign(array('target_user_id', 'target_user_login', 'target_user_email', 'notify'), 
						array($target_user_id, $user->get('login', $target_user_id), $user->get('email', $target_user_id), $notify), 
						$theme_delete);
			}
		}
		elseif($admin->getAction() == "group")
		{
			if($system->post('addgroup'))
			{
				$stmt = $database->con()->prepare("SELECT MAX(group_id) FROM {$constant->db['prefix']}_user_access_level");
				$stmt->execute();
				$resTemp = $stmt->fetch();
				$lastGroupId = $resTemp[0];
				$stmt = null;
				$lastGroupId++;
				$new_group_name = "New Group";
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_access_level (`group_id`, `group_name`) VALUES(?, ?)");
				$stmt->bindParam(1, $lastGroupId, PDO::PARAM_INT);
				$stmt->bindParam(2, $new_group_name, PDO::PARAM_STR);
				$stmt->execute();
				$stmt = null;
			}
			elseif($system->post('remove'))
			{
				$key_for_delete = array_keys($system->post('remove'));
				$delete_group_id = $key_for_delete[0];
				if($system->isInt($delete_group_id))
				{
					$stmt = $database->con()->prepare("DELETE FROM {$constant->db['prefix']}_user_access_level WHERE group_id = ?");
					$stmt->bindParam(1, $delete_group_id, PDO::PARAM_INT);
					$stmt->execute();
				}
			}
			elseif($system->post('acesssave'))
			{
				$post_access_table = $system->post('access');
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_access_level");
				$stmt->execute();
				$fetchRow = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$stmt = null;
				foreach($fetchRow as $access_single)
				{
					$current_gid = $access_single['group_id'];
					$current_access = $post_access_table[$current_gid];
					$update_column_data = array();
					foreach($access_single as $column=>$value)
					{
						if($current_access[$column] == null)
						{
							$update_column_data[$column] = "0";
						}
						elseif($current_access[$column] == "on")
						{
							$update_column_data[$column] = "1";
						}
						else
						{
							$update_column_data[$column] = $current_access[$column];
						}
					}
					$query_prepared_setter = $system->prepareKeyDataToDbUpdate($update_column_data);
					$stmt = $database->con()->prepare("UPDATE `{$constant->db['prefix']}_user_access_level` SET $query_prepared_setter WHERE `group_id` = ?");
					$stmt->bindParam(1, $current_gid, PDO::PARAM_INT);
					$stmt->execute();
					$stmt = null;
				}
			}
			$action_page_title .= $language->get('admin_component_usercontrol_group');
			$group_theme = $template->tplget('usercontrol_group_manage', 'components/', true);
			$column_theme = $template->tplget('usercontrol_group_manage_th', 'components/', true);
			$checkbox_theme = $template->tplget('usercontrol_group_manage_checkbox', 'components/', true);
			$input_theme = $template->tplget('usercontrol_group_manage_input', 'components/', true);
			$delete_theme = $template->tplget('usercontrol_group_manage_delete', 'components/', true);
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_access_level");
			$stmt->execute();
			$resultFetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$columnNames = array();
			$rowContainer = array();
			$isFirstRun = true;
			foreach($resultFetch as $values)
			{
				$group_id = 0;
				$rowItems = array();
				foreach($values as $columnName=>$columnData)
				{
					if($columnName == "group_id")
					{
						$group_id = $columnData;
					}
					if($isFirstRun)
					{
						$columnNames[] = $template->assign(array('group_column_helptext', 'group_column_name'), array($language->get('admin_component_usercontrol_group_column_'.$columnName), $columnName), $column_theme);
					}
					if(($columnData == "0" || $columnData == "1") && $columnName != "group_id")
					{
						$checked = null;
						if($columnData == "1")
						{
							$checked = "checked";
						}
						$rowItems[] = $template->assign(array('checkbox_name', 'is_checked'), array("access[{$group_id}][{$columnName}]", $checked), $checkbox_theme);
					}
					else
					{
						$rowItems[] = $template->assign(array('input_value', 'input_name'), array($columnData, "access[{$group_id}][{$columnName}]"), $input_theme);
					}
				}
				$rowItems[] = $template->assign('group_id', $group_id, $delete_theme);
				$isFirstRun = false;
				$rowContainer[] = $rowItems;
			}
			$columnNames[] = $language->get('admin_component_usercontrol_group_column_th_action');
			$edit_table = $admin->tplRawTable($columnNames, $rowContainer);
			$work_body = $template->assign(array('edit_table', 'component_id'), array($edit_table, $admin->getID()), $group_theme);
		}
		elseif($admin->getAction() == "ban")
		{
			$notify = null;
			$continue_block = null;
			if($system->post('ipblock'))
			{
				$userip = $system->validIP($system->post('userip'));
				if($userip)
				{
					$except_time = strtotime($system->post('enddate'));
					// проверяем, возможно данный ip уже заблокирован, зачем нам дубли и попаболь?
					$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_block WHERE ip = ?");
					$stmt->bindParam(1, $userip, PDO::PARAM_STR);
					$stmt->execute();
					$resIpFetch = $stmt->fetch();
					$stmt = null;
					// запись уже есть
					if($resIpFetch[0] > 0)
					{
						$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_block SET express = ? WHERE ip = ?");
						$stmt->bindParam(1, $except_time, PDO::PARAM_INT);
						$stmt->bindParam(2, $userip, PDO::PARAM_STR);
						$stmt->execute();
						$stmt = null;
						$notify .= $template->stringNotify('success', $language->get('admin_component_usercontrol_ban_ip_refreshed'));
					}
					// иначе это новый бан
					else
					{
						$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_block (`ip`, `express`) VALUES (?, ?)");
						$stmt->bindParam(1, $userip, PDO::PARAM_STR);
						$stmt->bindParam(2, $except_time, PDO::PARAM_INT);
						$stmt->execute();
						$notify .= $template->stringNotify('success', $language->get('admin_component_usercontrol_ban_ip_setted'));
					}
				}
				else
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_usercontrol_ban_wrong_data'), true);
				}
			}
			elseif($system->post('idorloginblock'))
			{
				$idorlogin = $system->post('userdata');
				$stmt = $database->con()->prepare("SELECT id FROM {$constant->db['prefix']}_user WHERE id = ? or login = ?");
				$stmt->bindParam(1, $idorlogin, PDO::PARAM_STR);
				$stmt->bindParam(2, $idorlogin, PDO::PARAM_STR);
				$stmt->execute();
				if($rowUser = $stmt->fetch())
				{
					$target_id = $rowUser['id'];
					$continue_block = $template->assign('block_user_id', $target_id, $template->tplget('usercontrol_ban_pers', 'components/', true));
				}
				else
				{
					$notify .= $template->stringNotify('error', $language->get('admin_component_usercontrol_ban_wrong_data'), true);
				}
			}
			elseif($system->post('banuserid'))
			{
				// 2ая стадия блокировки
				$ban_user_id = $system->post('blockuserid');
				$ban_execpt_time = strtotime($system->post('enddate'));
				$stmt = $database->con()->prepare("SELECT DISTINCT ip FROM {$constant->db['prefix']}_statistic WHERE reg_id = ?");
				$stmt->bindParam(1, $ban_user_id, PDO::PARAM_INT);
				$stmt->execute();
				while($result = $stmt->fetch())
				{
					$bstmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_block(user_id, ip, express) VALUES (?, ?, ?)");
					$bstmt->bindParam(1, $ban_user_id, PDO::PARAM_INT);
					$bstmt->bindParam(2, $result['ip'], PDO::PARAM_STR);
					$bstmt->bindParam(3, $ban_execpt_time, PDO::PARAM_INT);
					$bstmt->execute();
					$bstmt = null;
				}
				$notify .= $template->stringNotify('success', $language->get('admin_component_usercontrol_ban_ip_setted'));
			}
			$action_page_title .= $language->get('admin_component_usercontrol_serviceban');
			$ban_theme = $template->tplget('usercontrol_ban', 'components/', true);
			$work_body .= $template->assign(array('notify', 'continue_block'), array($notify, $continue_block), $ban_theme);
		}
		$body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
		return $body_form;
	}

	private function getPageCount()
	{
		global $database,$constant;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user");
		$stmt->execute();
		$result = $stmt->fetch();
		return intval($result[0]/$this->list_count);
	}
}

?>