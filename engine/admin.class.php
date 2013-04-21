<?php

/**
 *
 * @author zenn
 * Класс отвечающий за административную панель управления
 */
class admin
{
	private $object = null;
	private $action = null;
	private $add = null;
	private $id = 0;
	private $page = 0;

	private $object_name = null;

	function __construct()
	{
		$this->object = $_GET['object'];
		$this->action = $_GET['action'];
		$this->add = $_GET['add'];
		$this->id = (int)$_GET['id'];
		$this->page = (int)$_GET['page'];
	}


	/**
	 * Загрузка админ панели. Возвращает скомпилированный вариант вместе с шаблоном.
	 */
	public function doload()
	{
		global $page,$user,$template,$system;
		if($user->get('id') == NULL)
		{
			$system->redirect('/login');
			exit();
		}
		elseif($user->get('access_to_admin') != 1)
		{
			$system->redirect();
			exit();
		}
		else
		{
			switch($this->object)
			{
				case "components":
					$page->setContentPosition('body', $this->loadComponents());
					break;
				case "modules":
					$page->setContentPosition('body', $this->loadModules());
					break;
				case "hooks":
					$page->setContentPosition('body', $this->loadHooks());
					break;
				default:
					$page->setContentPosition('body', $this->loadMainPage());
					break;
			}
			$template->set('username', $user->get('nick'));
		}
		$header = $this->foreachMenuPositions();
		$page->setContentPosition('header', $header);
		$template->init();
		return $template->compile();
	}

	private function loadHooks()
	{
		global $template,$language,$database,$constant;
		if($this->hook_exists())
		{
			// хук существует, обращаемся к настройке
		}
		else
		{
			$theme = $template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
					array($language->get('admin_hooks_title'), $language->get('admin_hooks_tab_all'), $language->get('admin_hooks_tab_enabled'), $language->get('admin_hooks_tab_dissabled'), $language->get('admin_hooks_tab_toinstall')),
					$template->tplget('extension_list', null, true));
			$thead = $template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
					array($language->get('admin_hooks_table_th_1'), $language->get('admin_hooks_table_th_2'), $language->get('admin_hooks_table_th_3'), $language->get('admin_hooks_table_th_4'),),
					$template->tplget('extension_thead', null, true));
			$tbody = $template->tplget('extension_tbody', null, true);
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_hooks");
			$stmt->execute();
			$prepare_theme = array();
			while($result = $stmt->fetch())
			{
				$config_link = "?object=hooks&id=".$result['id'];
				// вносим в список отключенных
				if($result['enabled'] == 0)
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array($config_link, '?object=hooks&id='.$result['id'].'&action=turn'), $template->tplget('manage_noactive', null, true));
					$prepare_theme['dissabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
							array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
							$tbody);
				}
				// иначе вносим в список включенных
				else
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=hooks&id='.$result['id'], '?object=hooks&id='.$result['id'].'&action=turn'), $template->tplget('manage_active', null, true));
					$prepare_theme['enabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
							array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
							$tbody);
				}
				// вносим в список не установленных
				if($result['installed'] == 0)
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=hooks&id='.$result['id'], '?object=hooks&id='.$result['id'].'&action=install'), $template->tplget('manage_install', null, true));
					$prepare_theme['toinstall'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
							array($result['id'], $result['name'], $result['description'], $iconset),
							$tbody);
				}
				$iconset = $template->assign('ext_config_link', '?object=hooks&id='.$result['id'], $template->tplget('manage_all', null, true));
				$prepare_theme['all'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
						array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
						$tbody);
			}
			$alllist = $template->assign('extension_tbody', $prepare_theme['all'], $thead);
			$toinstalllist = $template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
			$activelist = $template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
			$noactivelist = $template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
			$theme = $template->assign(
					array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
					array($alllist, $toinstalllist, $activelist, $noactivelist),
					$theme
			);
			return $theme;

		}
	}

	private function hook_exists()
	{
		global $database,$constant;
		if($this->id < 1)
		{
			return false;
		}
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_hooks WHERE id = ?");
		$stmt->bindParam(1, $this->id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		if($result[0] == 1)
		{
			return true;
		}
		return false;
	}

	private function loadModules()
	{
		global $template,$language,$database,$constant;
		if($this->module_exits())
		{
			// модуль существует, выгружаем backend
		}
		else
		{
			$theme = $template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
					array($language->get('admin_modules_title'), $language->get('admin_modules_tab_all'), $language->get('admin_modules_tab_enabled'), $language->get('admin_modules_tab_dissabled'), $language->get('admin_modules_tab_toinstall')),
					$template->tplget('extension_list', null, true));
			$thead = $template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
					array($language->get('admin_modules_table_th_1'), $language->get('admin_modules_table_th_2'), $language->get('admin_modules_table_th_3'), $language->get('admin_modules_table_th_4'),),
					$template->tplget('extension_thead', null, true));
			$tbody = $template->tplget('extension_tbody', null, true);
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_modules");
			$stmt->execute();
			$prepare_theme = array();
			while($result = $stmt->fetch())
			{
				$config_link = "?object=modules&id=".$result['id'];
				// вносим в список отключенных
				if($result['enabled'] == 0)
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array($config_link, '?object=modules&id='.$result['id'].'&action=turn'), $template->tplget('manage_noactive', null, true));
					$prepare_theme['dissabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
							array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
							$tbody);
				}
				// иначе вносим в список включенных
				else
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=modules&id='.$result['id'], '?object=modules&id='.$result['id'].'&action=turn'), $template->tplget('manage_active', null, true));
					$prepare_theme['enabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
							array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
							$tbody);
				}
				// вносим в список не установленных
				if($result['installed'] == 0)
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=modules&id='.$result['id'], '?object=modules&id='.$result['id'].'&action=install'), $template->tplget('manage_install', null, true));
					$prepare_theme['toinstall'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
							array($result['id'], $result['name'], $result['description'], $iconset),
							$tbody);
				}
				$iconset = $template->assign('ext_config_link', '?object=modules&id='.$result['id'], $template->tplget('manage_all', null, true));
				$prepare_theme['all'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
						array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
						$tbody);
			}
			$alllist = $template->assign('extension_tbody', $prepare_theme['all'], $thead);
			$toinstalllist = $template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
			$activelist = $template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
			$noactivelist = $template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
			$theme = $template->assign(
					array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
					array($alllist, $toinstalllist, $activelist, $noactivelist),
					$theme
			);
			return $theme;
		}
	}

	private function module_exits()
	{
		global $database,$constant;
		if($this->id < 1)
		{
			return false;
		}
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_modules WHERE id = ?");
		$stmt->bindParam(1, $this->id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		if($result[0] == 1)
		{
			return true;
		}
		return false;
	}

	private function loadComponents()
	{
		global $template,$language,$database,$constant;
		if($this->component_exists())
		{
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_components WHERE id = ?");
			$stmt->bindParam(1, $this->id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch();
			$component_back = $constant->root.'/extensions/components/'.$result['dir'].'/back.php';
			$backend_config = null;
			if(file_exists($component_back))
			{
				$this->object_name = $result['name'];
				require_once($component_back);
				$class = "com_{$result['dir']}_back";
				$init = new $class;
				$backend_config = $init->load();
			}
			if($backend_config == null || strlen($backend_config) < 1)
			{
				$backend_config = $this->showNull();
			}
			return $backend_config;
		}
		else
		{
			// такого компонента нет, отображаем списки
			$theme = $template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
					array($language->get('admin_components_title'), $language->get('admin_components_tab_all'), $language->get('admin_components_tab_enabled'), $language->get('admin_components_tab_dissabled'), $language->get('admin_components_tab_toinstall')),
					$template->tplget('extension_list', null, true));
			$thead = $template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
					array($language->get('admin_components_table_th_1'), $language->get('admin_components_table_th_2'), $language->get('admin_components_table_th_3'), $language->get('admin_components_table_th_4'),),
					$template->tplget('extension_thead', null, true));
			$tbody = $template->tplget('extension_tbody', null, true);
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_components");
			$stmt->execute();
			$prepare_theme = array();
			while($result = $stmt->fetch())
			{
				$config_link = "?object=components&id=".$result['id'];
				// вносим в список отключенных
				if($result['enabled'] == 0)
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id='.$result['id'], '?object=components&id='.$result['id'].'&action=turn'), $template->tplget('manage_noactive', null, true));
					$prepare_theme['dissabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
							array($result['id'], $result['name'], $result['description'], $iconset),
							$tbody);
				}
				// иначе вносим в список включенных
				else
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id='.$result['id'], '?object=components&id='.$result['id'].'&action=turn'), $template->tplget('manage_active', null, true));
					$prepare_theme['enabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
							array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
							$tbody);
				}
				// вносим в список не установленных
				if($result['installed'] == 0)
				{
					$iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id='.$result['id'], '?object=components&id='.$result['id'].'&action=install'), $template->tplget('manage_install', null, true));
					$prepare_theme['toinstall'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
							array($result['id'], $result['name'], $result['description'], $iconset),
							$tbody);
				}
				$iconset = $template->assign('ext_config_link', '?object=components&id='.$result['id'], $template->tplget('manage_all', null, true));
				$prepare_theme['all'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
						array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
						$tbody);
			}
			$alllist = $template->assign('extension_tbody', $prepare_theme['all'], $thead);
			$toinstalllist = $template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
			$activelist = $template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
			$noactivelist = $template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
			$theme = $template->assign(
					array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
					array($alllist, $toinstalllist, $activelist, $noactivelist),
					$theme
			);
			return $theme;
		}
	}

	private function showNull()
	{
		global $template;
		return $template->tplget('nullcontent', null, true);
	}

	private function component_exists()
	{
		global $database,$constant;
		if($this->id < 1)
		{
			return false;
		}
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_components WHERE id = ?");
		$stmt->bindParam(1, $this->id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		if($result[0] == 1)
		{
			return true;
		}
		return false;
	}

	private function foreachMenuPositions()
	{
		global $template,$database,$constant,$language;
		$theme = $template->tplget('header', null, true);
		$list_theme = $template->tplget('list', null, true);
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_modules WHERE enabled = 1 LIMIT 5");
		$stmt->execute();
		$module_list = null;
		$component_list = null;
		$hook_list = null;
		while($result = $stmt->fetch())
		{
			$module_list .= $template->assign(array('list_href', 'list_text'), array("?object=modules&id={$result['id']}", $result['name']), $list_theme);
		}
		$module_list .= $template->assign(array('list_href', 'list_text'), array("?object=modules", $language->get('admin_nav_more_link')), $list_theme);
		$stmt = null;
		$result = null;
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_components WHERE enabled = 1 LIMIT 5");
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$component_list .= $template->assign(array('list_href', 'list_text'), array("?object=components&id={$result['id']}", $result['name']), $list_theme);
		}
		$component_list .= $template->assign(array('list_href', 'list_text'), array("?object=components", $language->get('admin_nav_more_link')), $list_theme);
		$stmt = null;
		$result = null;
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_hooks WHERE enabled = 1 LIMIT 5");
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$hook_list .= $template->assign(array('list_href', 'list_text'), array("?object=hooks&id={$result['id']}", $result['name']), $list_theme);
		}
		$hook_list .= $template->assign(array('list_href', 'list_text'), array("?object=hooks", $language->get('admin_nav_more_link')), $list_theme);
		$theme = $template->assign(array('module_list', 'component_list', 'hook_list'), array($module_list, $component_list, $hook_list), $theme);
		return $theme;
	}

	private function loadMainPage()
	{
		global $database,$constant,$template;
		list($month,$day,$year) = explode('-', date('m-d-y'));
		$start = mktime(0, 0, 0, $month, $day, $year);
		$end = mktime(0, 0, 0, $month, $day+1, $year);
		$stmt1 = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
		$stmt1->bindParam(1, $start);
		$stmt1->bindParam(2, $end);
		$stmt1->execute();
		$res1 = $stmt1->fetch();
		$views_count = $res1[0];
		$stmt2 = $database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
		$stmt2->bindParam(1, $start);
		$stmt2->bindParam(2, $end);
		$stmt2->execute();
		$res2 = $stmt2->fetch();
		$unique_user = $res2[0];
		$stmt3 = $database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$constant->db['prefix']}_statistic WHERE time >= ? and time <= ? AND isreg = 1");
		$stmt3->bindParam(1, $start);
		$stmt3->bindParam(2, $end);
		$stmt3->execute();
		$res3 = $stmt3->fetch();
		$unique_registered = $res3[0];
		$body = $template->tplget('index_page', null, true);
		$template->globalset('view_count', $views_count);
		$template->globalset('user_unique', $unique_user);
		$template->globalset('unique_registered', $unique_registered);
		$template->globalset('unique_unregistered', $unique_user-$unique_registered);
		$template->globalset('date_today', date('d-m-y'));
		$template->globalset('server_os_type', php_uname('s'));
		$template->globalset('server_php_ver', phpversion());
		$template->globalset('server_mysql_ver', $database->con()->getAttribute(PDO::ATTR_SERVER_VERSION));
		$template->globalset('server_load_avg', $this->get_server_load());
		$this->showWeekChart();
		$template->globalset('folder_uploads_access', $this->analiseAccess("/upload/", "rw"));
		$template->globalset('folder_language_access', $this->analiseAccess("/language/", "rw"));
		$template->globalset('folder_cache_access', $this->analiseAccess("/cache/", "rw"));
		$template->globalset('file_config_access', $this->analiseAccess("/config.php", "rw"));
		return $body;
	}

	private function showWeekChart()
	{
		global $database,$constant,$template,$language;
		$json_result = null;
		list($month,$day,$year) = explode('-', date('m-d-y'));
		for($i=5;$i>=0;$i--)
		{
			$totime = strtotime(date('Y-m-d', time() - ($i * 86400)));
			$fromtime = $totime - (60*60*24);
			$stmt1 = $database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
			$stmt1->bindParam(1, $fromtime);
			$stmt1->bindParam(2, $totime);
			$stmt1->execute();
			$res1 = $stmt1->fetch();
			$unique_users = $res1[0];
			$stmt2 = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
			$stmt2->bindParam(1, $fromtime);
			$stmt2->bindParam(2, $totime);
			$stmt2->execute();
			$res2 = $stmt2->fetch();
			$view_users = $res2[0];
			$object_date = date('d.m.Y', $fromtime);
			$json_result .= "['{$object_date}', {$view_users}, {$unique_users}],\n";
		}
		$template->globalset('json_chart_result', $json_result);
	}

	private function analiseAccess($data, $rule = 'rw')
	{
		global $constant;
		$error = false;
		for($i=0;$i<strlen($rule);$i++)
		{
			if($rule[$i] == "r")
			{
				if(!is_readable($constant->root.$data))
					$error = true;
			}
			elseif($rule[$i] == "w")
			{
				if(!is_writable($constant->root.$data))
					$error = true;
			}
		}
		if($error)
		{
			return "Error";
		}
		else
		{
			return "Ok";
		}
	}

	public function tplSettingsSelectYorN($variable_name, $variable_pseudo_name = null, $variable_desc = null, $selected = false)
	{
		global $template;
		if($variable_pseudo_name == null)
			$variable_pseudo_name = $variable_name;
		$selected_yes = null;
		$selected_no = null;
		$selected ? $selected_yes = "selected" : $selected_no = "selected";
		$theme = $template->assign(array('ext_config_name', 'ext_label', 'ext_description', 'selected_yes', 'selected_no'),
				array($variable_name, $variable_pseudo_name, $variable_desc, $selected_yes, $selected_no),
				$template->tplget('config_block_select_yorn', null, true));
		return $theme;
	}

	public function tplSettingsDirectory($data)
	{
		global $template;
		return $template->assign('config_directory', $data, $template->tplget('config_block_spacer', null, true));
	}

	/**
	 * Подгрузка блока конфигураций с определенным типом - input.text
	 * @param String $variable_name
	 * @param String $variable_value
	 * @param String $variable_pseudo_name
	 * @param String $variable_desc
	 * @return mixed
	 */
	public function tplSettingsInputText($variable_name, $variable_value = null, $variable_pseudo_name = null, $variable_desc = null)
	{
		global $template;
		if($variable_pseudo_name == null)
			$variable_pseudo_name = $variable_name;
		$theme = $template->assign(array('ext_config_name', 'ext_config_value', 'ext_label', 'ext_description'),
				array($variable_name, $variable_value, $variable_pseudo_name, $variable_desc),
				$template->tplget('config_block_input_text', null, true));
		return $theme;
	}
	/**
	 * Быстрая отрисовка таблицы. Параметры должны быть равноценными по сайзу массивами.
	 * @param array $columns - названия столбцов, массив. ['Column 1', 'Column 2' ... 'Column n']
	 * @param array $tbody - содержимое строк с столбцами array[0] = array(column 1, column 2 ... column n);array[1] = (); ...
	 */
	public function tplRawTable($columns, $tbody)
	{
		global $template,$language;
		if(is_array($columns) && is_array($tbody))
		{
			$thead = $template->tplget('rawtable_thead', null, true);
			$th_column = $template->tplget('rawtable_thcolumn', null, true);
			$tbody_tr = $template->tplget('rawtable_tbody', null, true);
			$td_raw_data = $template->tplget('rawtable_tdcolumn', null, true);
			$th_raw_result = null;
			foreach($columns as $th_data)
			{
				$th_raw_result .= $template->assign('raw_column', $th_data, $th_column);
			}
			// разбивка всего массива на строки tr
			$full_body_result = null;
			foreach($tbody as $tr_data)
			{
				$tr_contains = null;
				// Разбивка 1 строки tr на единичные колонки td
				foreach($tr_data as $td_data)
				{
					$tr_contains .= $template->assign('this_td', $td_data, $td_raw_data);
				}
				$full_body_result .= $template->assign('raw_td', $tr_contains, $tbody_tr);
			}
			return $template->assign(array('raw_th', 'raw_tbody'), array($th_raw_result, $full_body_result), $thead);
		}
		else
		{
			return;
		}
	}

	/**
	 * Отрисовка быстрой пагинации на страницах админки где она необходима
	 * @param unknown_type $list_count
	 * @param unknown_type $pagination_page_count
	 * @param unknown_type $uri_object
	 * @return NULL
	 */
	public function tplRawPagination($list_count, $pagination_page_count, $uri_object = "components")
	{
		global $template;
		$pagination_list_theme = $template->tplget('list_pagination', 'components/', true);
		$ret_position = intval($this->page/$list_count);
		$pagination_list = null;
		if($pagination_page_count <= 10 || $ret_position < 5)
		{
			for($i=0;$i<=$pagination_page_count;$i++)
			{
				$link_page = $i*$list_count;
				$pagination_list .= $template->assign(
						array('ext_pagination_href', 'ext_pagination_index'),
						array('?object='.$uri_object.'&id='.$this->id.'&action=list&page='.$link_page, $i),
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
						array('?object='.$uri_object.'&id='.$this->id.'&action=list&page='.$start_ret*$list_count, $start_ret),
						$pagination_list_theme);
			}
			// 0
			$pagination_list .= $template->assign(
					array('ext_pagination_href', 'ext_pagination_index'),
					array('?object='.$uri_object.'&id='.$this->id.'&action=list&page='.$ret_position*$list_count, $ret_position),
					$pagination_list_theme);
			$ret_position++;
			// 0>+3
			for(;$ret_position<=$end_ret;$ret_position++)
			{
				if($ret_position <= $pagination_page_count)
				{
					$pagination_list .= $template->assign(
							array('ext_pagination_href', 'ext_pagination_index'),
							array('?object='.$uri_object.'&id='.$this->id.'&action=list&page='.$ret_position*$list_count, $ret_position),
							$pagination_list_theme);
				}
			}

		}
		return $pagination_list;
	}

	/**
	 * Сохранение конфигов расширения. Обязателен формат: config:name_of_config
	 */
	public function trySaveConfigs()
	{
		global $system,$database,$constant;
		// увы, но PHP::PDO не хочет в prepared указывать аргумент имени таблицы :( поэтому ручками
		$table_name = $constant->db['prefix']."_";
		switch($this->object)
		{
			case "components":
			case "hooks":
			case "modules":
				$table_name .= $this->object;
				break;
		}
		$config_array = array();
		foreach($system->post(null) as $key => $data)
		{
			list($config_extension, $config_name) = explode(":", $key);
			if($config_extension == "config" && strlen($config_name) > 0)
			{
				$config_array[$config_name] = $data;
			}
		}
		$config_sql = serialize($config_array);
		try
		{
			$stmt = $database->con()->prepare("UPDATE $table_name SET configs = ? WHERE id = ?");
			$stmt->bindParam(1, $config_sql, PDO::PARAM_STR);
			$stmt->bindParam(2, $this->id, PDO::PARAM_INT);
			$stmt->execute();
		}
		catch(Exception $s)
		{
			return FALSE;
		}
		return true;
	}

	/**
	 * Обвертка для выгрузки конфига из расширений
	 * @param unknown_type $name
	 */
	public function getConfig($name, $var_type = null)
	{
		global $extension;
		return $extension->getConfig($name, $this->id, $this->object, $var_type);
	}

	private function get_server_load() {

		if (stristr(PHP_OS, 'win'))
		{
			return "WIN ERROR";

		}
		else
		{
			$sys_load = sys_getloadavg();
			$load = $sys_load[0];
		}
		return $load;
	}

	public function getID()
	{
		return $this->id;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getPage()
	{
		return $this->page;
	}

	public function getExtName()
	{
		return $this->object_name;
	}

}

?>