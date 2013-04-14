<?php

/**
 * Класс отвечающий за расширения - модули, компоненты и хуки.
 * @author zenn
 *
 */
class extension
{
	
	private $registeredway = array();
	private $notifyModuleAfter = array();
	private $config_loaded = null;
	
	function __construct()
	{
		$this->rawcomponents();
	}
	
	/**
	 * Вызгрузка списка компонентов и инклюдинг.
	 */
	private function rawcomponents()
	{
		global $constant,$database;
		$stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_components WHERE enabled = 1 AND installed = 1");
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$component_front = $constant->root.'/extensions/components/'.$result['dir'].'/front.php';
			if(file_exists($component_front))
			{
				require_once($component_front);
			}
			else
			{
				// Добавить нотификацию админа о кривом компоненте
			}
		}
	}
	
	/**
	 * Регистрация путей для компонентов
	 * Для первого параметра(пути) возможен массив путей registerPathWay(array('login', 'registration', 'recovery), 'usercontrol')
	 */
	public function registerPathWay($way, $dir)
	{
		if($way == null)
		{
			return false;
		}
		if(is_array($way))
		{
			foreach($way as $pathset)
			{
				if(array_key_exists($pathset, $this->registeredway))
				{
					return false;
				}
				$this->registeredway[$pathset] = $dir;
			}
			return true;
		}
		else
		{
			if($this->registeredway != null && array_key_exists($way, $this->registeredway))
			{
				return false;
			}
			$this->registeredway[$way] = $dir;
			return true;
		}
	}
	
	/**
	 * Сборка модулей страницы, до сборки всех позиций.
	 */
	public function modules_before_load()
	{
		global $constant,$database,$page;
		$stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_modules WHERE enabled = 1");
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			//обработка разрешенных и запрещенных урлов
			// 1 - работает там, где path_allowed, на других - нет
			$work_on_this_path = false;
			if($result['path_choice'] == 1)
			{
				// 	 { $this->stringPathway: component/aaa/ddd.html
				//	<   											=> ok
				//	 { module_rule: component/*
				$allowed_array = explode(';', $result['path_allow']);
				foreach($allowed_array as $allowed)
				{
					// если найдено вхождение, ставим маркер на true
					// нельзя выставить сразу, т.к. если в дальнейших маршрутах
					// не будет найдено вхождение, оно перекроет предидущее.
					$canwork = $page->findRuleInteration($allowed);
					if($canwork)
					{
						$work_on_this_path = true;
					}
				}
			}
			// обработка списка запрещенных урлов, на других - работаем
			else
			{
				$find_deny = false;
				$deny_array = explode(';', $result['path_deny']);
				foreach($deny_array as $deny)
				{
					if($page->findRuleInteration($deny))
					{
						$find_deny = true;
					}
				}
				$work_on_this_path = !$find_deny;
			}
			// если модуль работает в данной позиции, инициируем загрузку before()
			// которая работает до определения глобальной $template->content суперпозиции
			if($work_on_this_path)
			{
				$file = $constant->root.'/extensions/modules/'.$result['dir'].'/front.php';
				if(file_exists($file))
				{
					require_once($file);
					$mod_class = "mod_{$result['dir']}_front";
					$load_module = new $mod_class;
					// делаем маркер на модуль, который необходимо подгрузить в позиции after()
					$this->notifyModuleAfter[] = $load_module;
					// если функция before определена, вызываем его
					if(method_exists($load_module, 'before'))
					{
						$load_module->before();
					}
				}
				else
				{
					// мягкая нотификация админа
				}
			}
		}
	}
	
	/**
	 * Пост-загрузка метода after() модулей. Оперируют с полной страницей по template::content
	 */
	public function moduleAfterLoad()
	{
		foreach($this->notifyModuleAfter as $module)
		{
			if(method_exists($module, 'after'))
			{
				$module->after();
			}
		}
	}
	
	/**
	 * Список зарегистрированных URI
	 * @return multitype: array
	 */
	public function getRegisteredPathway()
	{
		return $this->registeredway;
	}
	
	/**
	 * Инициация главного метода load компонентов
	 * @return boolean
	 */
	public function initComponent()
	{
		global $page;
		$pathway = $page->getPathway();
		foreach($this->registeredway as $com_path=>$com_dir)
		{
			if($pathway[0] == $com_path)
			{
				$class_com_name = "com_{$com_dir}_front";
				$init_class = new $class_com_name;
				$init_class->load();
				// вхождение в путь найдено, дальнейшая обработка не нужна.
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Получение значения конфигурации
	 * @param unknown_type $name
	 */
	public function getConfig($name, $ext_id_or_dir, $object, $var_type = null)
	{
		global $system;
		$configs = unserialize($this->loadConfigs($ext_id_or_dir, $object));
		if($var_type == "boolean")
		{
			return $configs[$name] == "0" ? false : true;
		}
		elseif($var_type == "int")
		{
			return $system->toInt($configs[$name]);
		}
		return $configs[$name];
	}
	
	private function loadConfigs($id_dir, $object)
	{
		global $database,$constant;
		if($this->config_loaded == null || $this->config[$object][$id_dir] == null)
		{
			$table_name = $constant->db['prefix']."_";
			switch($object)
			{
				case "components":
				case "hooks":
				case "modules":
					$table_name .= $object;
					break;
				default:
					return;
			}
			$stmt = $database->con()->prepare("SELECT configs FROM $table_name WHERE id = ? OR dir = ?");
			$stmt->bindParam(1, $id_dir, PDO::PARAM_STR);
			$stmt->bindParam(2, $id_dir, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch();
			$this->config[$object][$id_dir] = $result['configs'];
		}
		return $this->config[$object][$id_dir];
	}
	
}

// зачатки интерфейсов для наследования расширениями
interface com_front
{
	public function load();
}
interface mod_front
{
	public function before();
	public function after();
}
interface hook_front
{
	public function load();
}



?>