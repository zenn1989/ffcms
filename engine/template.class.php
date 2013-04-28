<?php

/**
 * Класс шаблонизатора системы
 */
class template
{
	private $separator = "/";

	private $header = array();
	private $left = array();
	private $body = array();
	private $right = array();
	private $bottom = array();
	private $footer = array();

	private $content = null;
	private $debug_readcount = 0;

	private $precompile_tag = array();


	function __construct()
	{
		if(loader != 'api')
		{
			$this->content = $this->getCarcase();
		}
	}

	/**
	 * Инициация шаблонизатора. Загрузка стандартных блоков.
	 * Данные по каждой позиции расположены в page.class.php
	 */
	public function init()
	{
		global $page,$extension;
		if(loader == 'front')
		{
			// инициация пре-загружаемых модулей с возможностью $page::setContentPosition(pos, data, index)
			$extension->modules_before_load();
		}
		$this->header = $page->getContentPosition('header');
		$this->left = $page->getContentPosition('left');
		$this->right = $page->getContentPosition('right');
		$this->bottom = $page->getContentPosition('bottom');
		$this->footer = $page->getContentPosition('footer');
		$this->body = $page->getContentPosition('body');
	}

	/**
	 * Сборка и отображение шаблона
	 */
	public function compile()
	{
		global $cache,$extension,$constant;
		$this->fortpl('header');
		$this->fortpl('left');
		$this->fortpl('right');
		$this->fortpl('bottom');
		$this->fortpl('footer');
		$this->fortpl('body');
		if(loader == 'front')
		{
			// инициация пост-загружаемых модулей
			$extension->moduleAfterLoad();
		}
		$this->postcompile();
		$this->language();
		$this->ruleCheck();
		$this->htmlhead();
		$this->cleanvar();
		if($constant->do_compress_html && loader == 'front')
		{
			$this->compress();
		}
		if(loader == 'back')
		{
			$cache->save($this->content);
		}
		return $this->content;
	}

	/**
	 * Пост-компиляция массива заданных тегов
	 */
	private function postcompile()
	{
		foreach($this->precompile_tag as $tag=>$value)
		{
			$this->set($tag, $value);
		}
	}

	/**
	 * Пост компиляция тегов, после сборки шаблона
	 * @param String $tag
	 * @param String $value
	 */
	public function globalset($tag, $value)
	{
		$this->precompile_tag[$tag] = $value;
	}

	/**
	 * Установка всех значений 1 блока по имени блока.
	 */
	private function fortpl($position_name)
	{
		$result = null;
		$sort_entery = $this->{$position_name};
		if(count($sort_entery) > 0)
		{
			foreach($sort_entery as $enteries)
			{
				$result .= $enteries;
			}
		}
		$this->set($position_name, $result);
	}

	/**
	 * Установка языковых переменных
	 */
	private function language()
	{
		global $language;
		$this->content = $language->set($this->content);
	}

	/**
	 * Загрузка основного каркаса шаблона, main.tpl
	 */
	private function getCarcase()
	{
		$isadmin = (loader == 'back') ? true : false;
		return $this->tplget('main', null, $isadmin);
	}

	/**
	 * Назначение переменной в супер-позиции $content
	 * @param unknown_type $var
	 * @param unknown_type $value
	 */
	public function set($var, $value)
	{
		if(is_array($var))
		{
			for($i=0;$i<=sizeof($var);$i++)
			{
				$this->content = str_replace('{$'.$var[$i].'}', $value[$i], $this->content);
			}
		}
		else
		{
			$this->content = str_replace('{$'.$var.'}', $value, $this->content);
		}
	}

	/**
	 * Очистка от {$__?___} в результате.
	 * Для контента, обязательно использовать эквивалент -> $ = &#36;
	 */
	private function cleanvar()
	{
		$this->content = preg_replace('/{\$(.*?)}/s', '', $this->content);
	}

	/**
	 * Сжатие страницы путем удаления излишних переносов строк. Алгоритм героинский, но работает исправно и не бьет юзерский JS в коде шаблонов.
	 */
	private function compress()
	{
		$compressed = null;
		preg_match_all('/(.*?)\n/s', $this->content, $matches);
		foreach($matches[0] as $string)
		{
			if(preg_match('/[^\s]/s', $string))
				$compressed .= $string;
		}
		$this->content = $compressed;
	}

	/**
	 * Функция для обработки условий {$if условие}содержимое{/$if} в шаблонах
	 */
	private function ruleCheck()
	{
		global $rule;
		preg_match_all('/{\$if (.+?)}(.*?){\$\/if}/s', $this->content, $matches);
		// $matches[0][$i] - все
		// $matches[1][$i] - условие
		// $matches[2][$i] - содержимое
		for($i=0;$i<sizeof($matches[1]);$i++)
		{
			$theme_result = null;
			if($rule->getInstance()->check($matches[1][$i]))
			{
				$theme_result = $matches[2][$i];
			}
			$this->content = str_replace($matches[0][$i], $theme_result, $this->content);
		}
	}
	
	private function htmlhead()
	{
		global $constant,$system;
		$compiled_header = null;
		// сборка подключения файлов javascript в шапку, к примеру из тела компонента, когда непосредственного доступа к тегу <head></head> нет.
		// пр: {$jsfile lib/js/script.js} уйдет в <head><script src="url/tpl_dir/tpl_name/lib/js/script.js
		preg_match_all('/{\$jsfile (.*?)}/s', $this->content, $jsfile_matches);
		$jsfile_array = $system->nullArrayClean(array_unique($jsfile_matches[1]));
		foreach($jsfile_array as $jsfile)
		{
			if(file_exists($constant->root.$constant->ds.$constant->tpl_dir.$constant->ds.$constant->tpl_name.$constant->ds.$jsfile))
				$compiled_header .= "<script type=\"text/javascript\" src=\"{$constant->url}/{$constant->tpl_dir}/{$constant->tpl_name}/{$jsfile}\"></script>\r\n";
		}
		// сборка подключения CSS файлов в шапку, аналогично JS
		preg_match_all('/{\$cssfile (.*?)}/s', $this->content, $cssfile_matches);
		$cssfile_array = $system->nullArrayClean($cssfile_matches[1]);
		foreach($cssfile_array as $cssfile)
		{
			if(file_exists($constant->root.$constant->ds.$constant->tpl_dir.$constant->ds.$constant->tpl_name.$constant->ds.$cssfile))
				$compiled_header .= "<link href=\"{$constant->url}/{$constant->tpl_dir}/{$constant->tpl_name}/{$cssfile}\" rel=\"stylesheet\" />\r\n";
		}
		
		$this->content = str_replace('{$head_addons}', $compiled_header, $this->content);
	}

	/**
	 * Установка стандартных шаблоных переменных. Пример: {$url} => http://blabla
	 */
	public function setDefaults($theme, $isadmin = false)
	{
		global $constant,$user;
		if($isadmin)
		{
			$template_path = $constant->tpl_dir.$this->separator.$constant->admin_tpl;
		}
		else
		{
			$template_path = $constant->tpl_dir.$this->separator.$constant->tpl_name;
		}
		return str_replace(array('{$url}', '{$tpl_dir}', '{$user_id}', '{$user_nick}'),
				array($constant->url, $template_path, $user->get('id'), $user->get('nick')),
				$theme);
	}


	/**
	 * Загрузка файла шаблона.
	 * @param STRING $tplname Имя файла шаблона
	 * @param STRING $customdirectory Вложение в директорию внутри шаблона. Может быть пустым.
	 * @param BOOLEAN $isadmin Для использования в админ панели
	 */
	public function tplget($tplname, $customdirectory = null, $isadmin = false)
	{
		global $constant;
		if($isadmin)
		{
			$file = $constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->admin_tpl.$this->separator.$customdirectory.$tplname.".tpl";
		}
		else
		{
			$file = $constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->tpl_name.$this->separator.$customdirectory.$tplname.".tpl";
		}
		if(file_exists($file))
		{
			$this->debug_readcount++;
			return $this->setDefaults(file_get_contents($file), $isadmin);
		}
		return $this->tplException($tplname);
	}

	/**
	 * Назначение тегу значения (краткий аналог str_replace)
	 * @param unknown_type $tag
	 * @param unknown_type $data
	 * @param unknown_type $where
	 * @return mixed
	 */
	public function assign($tag, $data, $where)
	{
		if(is_array($tag))
		{
			$copy = array();
			foreach($tag as $entery)
			{
				$copy[] = '{$'.$entery.'}';
			}
			return str_replace($copy, $data, $where);
		}
		return str_replace('{$'.$tag.'}', $data, $where);
	}

	/**
	 * Выход при отсутствии файлов шаблона
	 */
	private function tplException($tpl)
	{
		exit("Template file not founded: ".$tpl);
	}

	/**
	 * Возвращает блок уведомлений
	 * @param ENUM('error', 'info', 'success') $type
	 * @param String $message
	 * @param Boolean $isadmin
	 * @return mixed
	 */
	public function stringNotify($type, $content, $isadmin = false)
	{
		$theme = $this->tplget("notify_string_{$type}");
		return $this->assign('content', $content, $theme);
	}

	/**
	 * Ошибка 404 для пользователей
	 */
	public function compile404()
	{
		global $cache;
		$cache->setNoExist(true);
		return $this->tplget('404');
	}

	/**
	 * USE template::stringNotify()
	 * @deprecated
	 */
	public function compileNotify($type, $text, $isadmin = false)
	{
		return $this->stringNotify($type, $text, $isadmin);
	}
	/**
	 * Отладочная информация о кол-ве считанных шаблонов
	 */
	public function getReadCount()
	{
		return $this->debug_readcount;
	}

	/**
	 * Очистка всех позиций. Возможна повторная выгрузка другого шаблона.
	 */
	public function cleanafterprint()
	{
		unset($this->content);
		unset($this->header);
		unset($this->left);
		unset($this->body);
		unset($this->right);
		unset($this->bottom);
		unset($this->footer);
	}
}
?>