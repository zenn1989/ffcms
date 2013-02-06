<?php

/**
 * Класс шаблонизатора системы
 */
class template
{
	private $separator = "/";

	/**
	* Загрузка каркаса шаблона
	*/
	public function carcase()
	{
		$main = $this->tplget('main');
		return $main;
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
	
	private function tplException($tpl)
	{
		exit("Template file not founded: ".$tpl);
	}




}
?>