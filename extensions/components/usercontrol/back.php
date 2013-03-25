<?php 
class com_usercontrol_back
{
	private $list_count = 10;
	public function load()
	{
		global $admin,$template,$language,$constant,$database;
		$action_page_title = $admin->getExtName()." : ";
		$menu_theme = $template->tplget('config_menu', null, true);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=list', $language->get('admin_component_usercontrol_manage')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=group', $language->get('admin_component_usercontrol_group')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=ban', $language->get('admin_component_usercontrol_ban')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=import', $language->get('admin_component_usercontrol_import')), $menu_theme);
		$work_body = null;
		if($admin->getAction() == "list" || $admin->getAction() == NULL)
		{
			$action_page_title .= $language->get('admin_component_usercontrol_manage');
			$index_start = $admin->getPage();
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user LIMIT ?,?");
			$stmt->bindParam(1, $index_start, PDO::PARAM_INT);
			$stmt->bindParam(2, $this->list_count, PDO::PARAM_INT);
			$stmt->execute();
			$c;
			while($res = $stmt->fetch())
			{
				$c .= $res['email']."<br />";
			}
			$work_body = $c;
		}
		$body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
		return $body_form;
	}
}

?>