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
	
	
	function __construct()
	{
		$this->content = $this->getCarcase();
	}

	/**
	* Инициация шаблонизатора. Загрузка стандартных блоков.
	* Данные по каждой позиции расположены в page.class.php
	*/
	public function init()
	{
		global $page;
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
		global $cache;
		$this->fortpl('header');
		$this->fortpl('left');
		$this->fortpl('right');
		$this->fortpl('bottom');
		$this->fortpl('footer');
		$this->fortpl('body');
		$this->cleanvar();
		$cache->save($this->content);
		return $this->content;
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
	* Загрузка основного каркаса шаблона, main.tpl
	*/
	private function getCarcase()
	{
		return $this->tplget('main');
	}
	
	public function set($var, $value)
	{
		$this->content = str_replace('{$'.$var.'}', $value, $this->content);
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
	private function setDefaults($theme)
	{
		global $constant;
		$template_path = $constant->tpl_dir.$this->separator.$constant->tpl_name;
		return str_replace(array('{$url}', '{$tpl_dir}'), array($constant->url, $template_path), $theme);
	}
	
	
	/**
	* Загрузка файла шаблона. 
	* Содержит 2 аргумента - имя шаблона, и возможный - отдельная директория с бекслешем на конце или $this->separator
	*/
	public function tplget($tplname, $customdirectory = null)
	{
		global $constant;
		$file = $constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->tpl_name.$this->separator.$customdirectory.$tplname.".tpl";
		if(file_exists($file))
		{
			$this->debug_readcount++;
			return $this->setDefaults(file_get_contents($file));
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