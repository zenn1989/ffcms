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

	/**
	* Инициация шаблонизатора. Загрузка стандартных блоков.
	* Данные по каждой позиции расположены в page.class.php
	*/
	public function init()
	{
		global $page;
		$this->header = $page->getHeader();
		$this->content = $this->getCarcase();
		$this->set('body', "Hello world");
	}

	/**
	* Сборка и отображение шаблона
	*/
	public function compile()
	{
		$this->fortpl('header');
		return $this->content;
	}
	
	/**
	* Установка всех значений 1 блока по имени блока.
	*/
	private function fortpl($position_name)
	{
		$result = null;
		if(count($this->{$position_name}) > 0)
		{
			foreach($this->{$position_name} as $enteries)
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
		return $this->setDefaults($this->tplget('main'));
	}
	
	private function set($var, $value)
	{
		$this->content = str_replace('{$'.$var.'}', $value, $this->content);
	}
	
	/**
	* Установка стандартных шаблоных переменных. Пример: {$url} => http://blabla
	*/
	public function setDefaults($theme)
	{
		global $constant;
		$template_path = $constant->tpl_dir.$this->separator.$constant->tpl_name;
		return str_replace(array('{$url}', '{$tpl_dir}'), array($constant->url, $template_path), $theme);
	}
	
	
	/**
	* Загрузка файла шаблона. 
	* Содержит 2 аргумента - имя шаблона, и возможный - отдельная директория с бекслешем на конце или $this->separator
	*/
	private function tplget($tplname, $customdirectory = null)
	{
		global $constant;
		$file = $constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->tpl_name.$this->separator.$customdirectory.$tplname.".tpl";
		if(file_exists($file))
		{
			return file_get_contents($file);
		}
		return $this->tplException($tplname);
	}
	
	/**
	* Выход при отсутствии файлов шаблона
	*/
	private function tplException($tpl)
	{
		exit("Template file not founded: ".$tpl);
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