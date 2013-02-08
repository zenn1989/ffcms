<?php

/**
 * Стандартный клас для работы элементами страницы
 */
class page
{
	private $pathway = array();
	private $registeredway = array();

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
		// если размер пачвея более 0 - передаем управление на компонент
		if(sizeof($this->pathway) > 0)
		{
			foreach($this->registeredway as $com_path=>$com_dir)
			{
				if($this->pathway[0] == $com_path)
				{
					$class_com_name = "com_{$com_dir}";
					$init_class = new $class_com_name;
					$result_class = $init_class->load();
				}
			}
		}
		$template->init();
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
			exit("Component list at /extensions/components/enabled not founded! Take care!");
			return;
		}
		$list = file_get_contents($enabled);
		$com_array = explode("\n", $list);
		foreach($com_array as $components)
		{
			$component_front = $constant->root.'/extensions/components/'.$components.'/front.php';
			if(!file_exists($component_front))
			{
				exit("Component $components directory not exists! Remove it from enabled list!");
				return;
			}
			require_once($component_front);
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
    
}


?>