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

	function __construct()
	{
		$this->object = $_GET['object'];
		$this->action = $_GET['action'];
		$this->add = $_GET['add'];
		$this->id = (int)$_GET['id'];
	}


	/**
	 * Загрузка админ панели. Возвращает скомпилированный вариант вместе с шаблоном.
	 */
	public function doload()
	{
		global $page,$user,$template,$system;
		if($user->getUserId() == 0)
		{
			$system->redirect('/login');
			exit();
		}
		elseif(!$user->CanAccessToAdmin())
		{
			$system->redirect();
			exit();
		}
		else
		{
			switch($object)
			{
				case "components":
					break;
				case "modules":
					break;
				case "hooks":
					break;
				default:
					$page->setContentPosition('body', $this->loadMainPage());
					break;
			}
			$template->set('username', $user->getUserName());
		}
		$header = $this->foreachMenuPositions();
		$page->setContentPosition('header', $header);
		$template->init();
		return $template->compile();
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
		$template->postsetTag('view_count', $views_count);
		$template->postsetTag('user_unique', $unique_user);
		$template->postsetTag('unique_registered', $unique_registered);
		$template->postsetTag('unique_unregistered', $unique_user-$unique_registered);
		$template->postsetTag('date_today', date('d-m-y'));
		$template->postsetTag('server_os_type', php_uname('s'));
		$template->postsetTag('server_php_ver', phpversion());
		$template->postsetTag('server_mysql_ver', $database->con()->getAttribute(PDO::ATTR_SERVER_VERSION));
		$template->postsetTag('server_load_avg', $this->get_server_load());
		$this->showWeekChart();
		$template->postsetTag('folder_uploads_access', $this->analiseAccess("/upload/", "rw"));
		$template->postsetTag('folder_language_access', $this->analiseAccess("/language/", "rw"));
		$template->postsetTag('folder_cache_access', $this->analiseAccess("/cache/", "rw"));
		$template->postsetTag('file_config_access', $this->analiseAccess("/config.php", "rw"));
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
		$template->postsetTag('json_chart_result', $json_result);
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

}

?>