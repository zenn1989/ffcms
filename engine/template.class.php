<?php

/**
 * Класс шаблонизатора системы
 */
class template
{
	private $separator = "/";
	
	private $pos_header = array();
	private $pos_left = array();
	private $pos_main = array();
	private $pos_right = array();
	private $pos_bottom = array();
	private $pos_footer = array();
	
	private $content = null;

	/**
	* Инициация шаблонизатора. Загрузка стандартных блоков.
	* Данные по каждой позиции расположены в page.class.php
	*/
	public function init()
	{
		$this->pos_main[] = $this->tplget("main");
	}

	/**
	* Сборка и отображение шаблона
	*/
	public function compile()
	{
		$this->fortpl('pos_main');
		return $this->content;
	}
	
	private function fortpl($position_name)
	{
		if(count($this->{$position_name}) > 0)
		{
			foreach($this->{$position_name} as $enteries)
			{
				$this->content .= $enteries;
			}
		}
	}
	
	
	/**
	* Загрузка файла шаблона. 
	* Содержит 2 аргумента - имя шаблона, и возможный - отдельная директория с бекслешем на конце или $this->separator
	*/
	private function tplget($tplname, $customdirectory = null)
	{
		global $constant;
		$file = file_get_contents($constant->root.$this->separator.$constant->tpl_dir.$this->separator.$constant->tpl_name.$this->separator.$customdirectory.$tplname.".tpl");
		if(!$file)
		{
			return $this->tplException($tplname);
		}
		return $file;
	}
	
	/**
	* Выход при отсутствии файлов шаблона
	*/
	private function tplException($tpl)
	{
		exit("Template file not founded: ".$tpl);
	}
	
	public function cleanafterprint()
	{
		unset($this->content);
	}
}
?>