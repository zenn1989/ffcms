<?php 
class com_news_back
{
	private $list_count = 10;
	
	public function load()
	{
		global $admin,$template,$language,$database,$constant;
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
			$form_table = $admin->tplRawTable(array('id', 'Заголовок', 'Ссылка', 'Управление'), $news_array);
			$work_body = $template->assign(array('ext_table_data'), array($form_table), $theme_list);
		}
		
		$body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
		return $body_form;
	}
}

?>