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
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=import', $language->get('admin_component_usercontrol_import')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=settings', $language->get('admin_component_usercontrol_settings')), $menu_theme);
		$work_body = null;
		if($admin->getAction() == "list" || $admin->getAction() == NULL)
		{
			$action_page_title .= $language->get('admin_component_usercontrol_manage');
			$index_start = $admin->getPage();
			$list_theme = $template->tplget('usercontrol_list', 'components/', true);
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user LIMIT ?,?");
			$stmt->bindParam(1, $index_start, PDO::PARAM_INT);
			$stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
			$stmt->execute();
			$user_data_array = array();
			while($res = $stmt->fetch())
			{
				$user_data_array[] = array($res['id'], $res['login'], $res['email'], '');
			}
			$table_result = $admin->tplrawTable(array('ID', 'Логин', 'Почта', 'Управление'), $user_data_array);
			$work_body = $template->assign(	array('ext_table_data', 'ext_search_value', 'ext_pagination_list'),	array($table_result, $system->post('search'), $pagination_list), $list_theme);
		}
		$body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
		return $body_form;
	}
}

?>