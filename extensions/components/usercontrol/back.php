<?php 
class com_usercontrol_back
{
	private $list_count = 10;
	public function load()
	{
		global $admin,$template,$language,$constant,$database,$system;
		$action_page_title = $admin->getExtName()." : ";
		$menu_theme = $template->tplget('config_menu', null, true);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=list', $language->get('admin_component_usercontrol_manage')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=group', $language->get('admin_component_usercontrol_group')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=ban', $language->get('admin_component_usercontrol_ban')), $menu_theme);
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
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user WHERE login like ? OR email like ? OR nick like ? LIMIT ?,?");
				$stmt->bindParam(1, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(2, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(3, $search_string, PDO::PARAM_STR);
				$stmt->bindParam(4, $index_start, PDO::PARAM_INT);
				$stmt->bindParam(5, $this->list_count, PDO::PARAM_INT);
				$stmt->execute();
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user LIMIT ?,?");
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
			
			$work_body .= $template->assign('ext_form', $config_set, $config_form);
				
		}
		elseif($admin->getAction() == "edit")
		{
			$action_page_title .= "Сисечки";
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