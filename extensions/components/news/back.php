<?php 
class com_news_back
{
	private $list_count = 10;
	
	public function load()
	{
		global $admin,$template,$language,$database,$constant,$system;
		$action_page_title = $admin->getExtName()." : ";
		$work_body = null;
		$menu_theme = $template->tplget('config_menu', null, true);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=list', $language->get('admin_component_news_manage')), $menu_theme);
		$menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=components&id='.$admin->getID().'&action=add', $language->get('admin_component_news_add')), $menu_theme);
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
}

?>