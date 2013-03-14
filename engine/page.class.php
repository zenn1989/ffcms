<?php

/**
 * Стандартный клас для работы элементами страницы
 */
class page
{
	private $pathway = array();
	private $registeredway = array();
	private $nocacheurl = array();
	
	private $string_pathway = null;
	
	private $content_body = array();
	private $content_header = array();
	private $content_left = array();
	private $content_right = array();
	private $content_bottom = array();
	private $content_footer = array();
	
	private $notifyModuleAfter = array();
	
	private $isMainPage = false;

	function __construct()
	{
		$this->rawuri();
		$this->rawcomponents();
	}
	
	/**
	* Обработка и вывод страницы. 
	*/
	public function doload()
	{
		global $template,$system,$cache,$user,$admin;
		$isComponent = false;
		// если пользователь не авторизован и есть полный кеш страницы
		if($user->get('id') == NULL && $cache->check())
		{
			return $cache->get();
		}
		// если размер пачвея более 0
		if(sizeof($this->pathway) > 0)
		{
			foreach($this->registeredway as $com_path=>$com_dir)
			{
				if($this->pathway[0] == $com_path)
				{
					$class_com_name = "com_{$com_dir}";
					$init_class = new $class_com_name;
					$init_class->load();
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
				$this->isMainPage = true;
				// на сейчас нет конструктора модулей, поэтому главная увы пустая с таким вот приветствием
				$this->content_body[] = "This is main page example";
			}
			// Нет? Не главная? Скомпилим 404
			else
			{
				$this->content_body[] = $template->compile404();
			}
		}
		// инициация шаблонизатора, нужно сделать умней!
		$template->init();	
	}
	
	/**
	* Вызгрузка списка компонентов и инклюдинг.
	*/
	private function rawcomponents()
	{
		global $constant,$database;
		$stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_components WHERE enabled = 1");
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$component_front = $constant->root.'/extensions/components/'.$result['dir'].'/front.php';
			if(!file_exists($component_front))
			{
				// завершение, однако в дальнейшем, нужно переработать этот механизм на мягкую нотификацию админа
				// когда возможно будет идентифицировать инициатора запроса как админа
				exit("Component frontend not founded at $component_front");
				return;
			}
			require_once($component_front);
		}
	}
	
	/**
	* Сборка модулей страницы, до сборки всех позиций.
	*/
	public function modules_before_load()
	{
		global $constant,$database;
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
					$canwork = $this->findRuleInteration($allowed);
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
					if($this->findRuleInteration($deny))
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
				if(!file_exists($file))
				{
					// далее - мягкое уведомление админа в будующем
					exit("Module $file not founded");
					return;
				}
				require_once($file);
				$mod_class = "mod_{$result['dir']}";
				$load_module = new $mod_class;
				// делаем маркер на модуль, который необходимо подгрузить в позиции after()
				$this->notifyModuleAfter[] = $load_module;
				// если функция before определена, вызываем его
				if(method_exists($load_module, 'before'))
				{
					$load_module->before();
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
	 * Boolean функция, отвечающая за то является ли данная страница главной
	 */
	public function isMain()
	{
		return $this->isMainPage;
	}
	
	/**
	* Функция по поиску вхождений в правилах урл-ов
	* к примеру /com/site/dddd/static.html является вхождением
	* в /com/*
	*/
	private function findRuleInteration($rule_way)
	{
		global $system;
		$rule_split = explode("/", $rule_way);
		for($i=0;$i<=sizeof($rule_split);$i++)
		{
			// если уровень правила содержит * - возвращаем истину
			if($rule_split[$i] == "*")
			{
				return true;
			}
			else
			{
				// если уровень правила и пачвея совпали
				if($rule_split[$i] == $this->pathway[$i])
				{
					// если это последний элемент пачвея
					if($system->contains('.html', $this->pathway[$i]))
					{
						return true;
					}
					// иначе - крутим дальше цикл
				}
				else
				{
					// если не совпали - возврат лжи
					return false;
				}
			}
		}
		// если после цикла нет точного определения, возвращаем лож, так как вхождения нет
		return false;
	}
	
	
	/**
	* Регистрация путей для компонентов
	* Для первого параметра(пути) возможен массив путей registerPathWay(array('login', 'registration', 'recovery), 'usercontrol') 
	*/
	public function registerPathWay($way, $dir)
	{
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
			if(array_key_exists($way, $this->registeredway))
			{
				return false;
			}
			$this->registeredway[$way] = $dir;
			return true;
		}
	}
	
	/**
	* Разобранный путь реквеста в массив по сплиту /
	*/ 
	public function getPathway()
	{
		return $this->pathway;
	}
	
	/**
	* Чистый пачвей для спец. нужд
	*/
	public function getStrPathway()
	{
		return $this->string_pathway;
	}
	
	/**
	* Запретить кеширование
	*/
	public function setNoCache($nullway)
	{
		$this->nocacheurl[] = $nullway;
	}
	
	/**
	* Получить запрещенные урл-ы для кеша
	*/
	public function getNoCache()
	{
		return $this->nocacheurl;
	}
	
	/**
	* Разбивка запроса от пользователя на массив.
	* Пример: /novosti/obshestvo/segodnya-sluchilos-prestuplenie.html приймет вид:
	* array(0 => 'novosti', 1 => 'obshestvo', 2 => 'segodnya-sluchilos-prestuplenie.html')
	*/
	private function rawuri()
	{
		$this->string_pathway = $_SERVER['REQUEST_URI'];
		$split = explode("/", $this->string_pathway);
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
	public function getContentPosition($position)
	{
		$pos = "content_{$position}";
		return $this->{$pos};
	}
	
	/**
	* Добавление к массиву позиций значения
	*/
	public function setContentPosition($position, $content, $index = null)
	{
		$var = "content_{$position}";
		if($index == null)
		{
			$this->{$var}[] = $content;
		}
		else
		{
			$this->{$var}[$index] = $content;
		}
	}    
}


?>