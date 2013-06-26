<?php

/**
 *
 * @author zenn
 * Класс управляющий мета-данными CMS (title,description,keywords)
 */
class meta
{
	private $title = array();
	private $description = array();
	private $keywords = array();

	/**
	 * Назначение содержимого мета-тега по имени тега
	 * @param String $data - присваиваемое содержимое
	 * @param ENUM $metaname - имя мета-тега
	 */
	public function set($metaname, $data)
	{
		$this->{$metaname} = null;
        $this->{$metaname}[] = $data;
	}

	/**
	 * Получение мета-тега по имени
	 * @param ENUM $metaname - имя мета-тега
	 */
	public function get($metaname)
	{
		return $this->{$metaname};
	}

	/**
	 * Добавление к мета-тегу содержимого по имени
	 * @param String $data
	 * @param ENUM $metaname
	 */
	public function add($metaname, $data)
	{
		$this->{$metaname}[] = $data;
	}
	
	public function compile()
	{
		global $template,$system;
		$template->globalset('keywords', $system->altimplode(", ", $this->keywords));
		$template->globalset('description', $system->altimplode(". ", $this->description));
		$template->globalset('title', $system->altimplode(" - ", array_reverse($this->title)));
	}

}


?>