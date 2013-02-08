<?php

/**
 * Стандартный клас для работы элементами страницы
 */
class page
{
	private $pathway = array();
	private $registeredway = array();
	
	private $content_body = array();

	function __construct()
	{
		$this->rawuri();
		$this->rawcomponents();
	}
	
	/**
	* Обработка и вывод страницы. 
	*/
	public function printload()
	{
		global $template,$system;
		$isComponent = false;
		// если размер пачвея более 0 и не содержит .html в тексте - передаем управление на компонент
		if(sizeof($this->pathway) > 0 && !$system->contains('.html', $this->pathway[0]))
		{
			foreach($this->registeredway as $com_path=>$com_dir)
			{
				if($this->pathway[0] == $com_path)
				{
					$class_com_name = "com_{$com_dir}";
					$init_class = new $class_com_name;
					$result_body = $init_class->load();
					// вхождение в путь найдено, дальнейшая обработка не нужна.
					$isComponent = true;
				}
			}
		}
		// вхождение по урлам не найдено. Кхм!
		if(!$isComponent)
		{
			// может быть это главная страничка?
			if(sizeof($this->pathway) == 0 || $system->contains('index.', $this->pathway[0]))
			{
				$result_body = "This is main page example";
			}
			// Нет? Не главная? Скомпилим 404
			else
			{
				$result_body = $template->compile404();
			}
		}
		$this->content_body[] = $result_body;
		// инициация шаблонизатора, нужно сделать умней!
		$template->init();
		// билдим модули
		$this->buildmodules();
		return $template->compile();	
	}
	
	/**
	* Вызгрузка списка компонентов и инклюдинг.
	*/
	private function rawcomponents()
	{
		global $constant;
		$enabled = $constant->root.'/extensions/components/enabled';
		if(!file_exists($enabled))
		{
			exit("Component list at <b>/extensions/components/enabled</b> not founded! Be care!");
			return;
		}
		$list = file_get_contents($enabled);
		$com_array = explode("\n", $list);
		foreach($com_array as $components)
		{
			$component_front = $constant->root.'/extensions/components/'.$components.'/front.php';
			if(!file_exists($component_front))
			{
				exit("Component <b>$components</b> not exists! Remove it from enabled list - <b>/extensions/components/enable</b> !");
				return;
			}
			require_once($component_front);
		}
	}
	
	/**
	* Сборка модулей страницы
	*/
	private function buildmodules()
	{
		global $constant;
		$file = $constant->root.'/extensions/modules/enabled';
		if(!file_exists($file))
		{
			exit("Module list at <b>/extensions/modules/enabled</b> not founded.");
			return;
		}
		$list = file_get_contents($file);
		$mod_array = explode("\n", $list);
		foreach($mod_array as $modules)
		{
			$module_front = $constant->root.'/extensions/modules/'.$modules.'/front.php';
			if(!file_exists($module_front))
			{
				exit("Module <b>$modules</b> not exists! Remove it from enabled list!");
				return;
			}
			require_once($module_front);
			$mod_class = "mod_{$modules}";
			$load_module = new $mod_class;
			$load_module->load();
		}
		
	}
	
	// регистрация путей от компонентов.
	public function registerPathWay($way, $dir)
	{
		if(array_key_exists($way, $this->registeredway))
		{
			return false;
		}
		$this->registeredway[$way] = $dir;
		return true;
	}
	
	/**
	* Разобранный путь реквеста в массив по сплиту /
	*/ 
	public function getPathway()
	{
		return $this->pathway;
	}
	
	/**
	* Разбивка запроса от пользователя на массив.
	* Пример: /novosti/obshestvo/segodnya-sluchilos-prestuplenie.html приймет вид:
	* array(0 => 'novosti', 1 => 'obshestvo', 2 => 'segodnya-sluchilos-prestuplenie.html')
	*/
	private function rawuri()
	{
		$split = explode("/", $_SERVER['REQUEST_URI']);
		foreach($split as $values)
		{
			if($values != null)
			{
				$this->pathway[] = $values;
			}
		}
	}
	
	
	
	/**
	* Возвращение массива позиции. Пример.
	*/
	public function getHeader()
	{
		return array('<p>', 'This is test', '</p>');
	}
	
	public function getBody()
	{
		return $this->content_body;
	}
    
}


?>