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
		$this->content = $this->getCarcase(isadmin);
	}

	/**
	* Инициация шаблонизатора. Загрузка стандартных блоков.
	* Данные по каждой позиции расположены в page.class.php
	*/
	public function init()
	{
		global $page,$extension;
		if(!isadmin)
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
		global $cache,$extension;
		$this->fortpl('header');
		$this->fortpl('left');
		$this->fortpl('right');
		$this->fortpl('bottom');
		$this->fortpl('footer');
		$this->fortpl('body');
		if(!isadmin)
		{
			// инициация пост-загружаемых модулей
			$extension->moduleAfterLoad();
		}
		$this->postcompile();
		$this->language();
		$this->cleanvar();
		if(!isadmin)
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
	
	private function removeUseLessTags()
	{
		$this->content = str_replace($this->registered_vars, '', $this->content);
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
	private function getCarcase($isadmin = FALSE)
	{
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
		$this->content = preg_replace('/{\$(.*)}/', '', $this->content);
	}
	
	/**
	* Установка стандартных шаблоных переменных. Пример: {$url} => http://blabla
	*/
	private function setDefaults($theme, $isadmin)
	{
		global $constant;
		if($isadmin)
		{
			$template_path = $constant->tpl_dir.$this->separator.$constant->admin_tpl;
		}
		else 
		{
			$template_path = $constant->tpl_dir.$this->separator.$constant->tpl_name;
		}
		return str_replace(array('{$url}', '{$tpl_dir}'), array($constant->url, $template_path), $theme);
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
	* Функция для строчных уведомлений
	* $type - error, warning, success
	*/
	public function stringNotify($type, $content)
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