<?php

/**
 *
 * @author zenn
 * Класс управляющий мета-данными CMS (title,description,keywords)
 */
class meta
{
	private $title = null;
	private $description = null;
	private $keywords = null;

	/**
	 * Назначение содержимого мета-тега по имени тега
	 * @param String $data - присваиваемое содержимое
	 * @param ENUM $metaname - имя мета-тега
	 */
	public function set($metaname, $data)
	{
		$this->{$metaname} = $data;
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
		$this->{$metaname} .= $data;
	}
	
	public function compile()
	{
		global $template;
		$template->globalset('keywords', $this->keywords);
		$template->globalset('description', $this->description);
		$template->globalset('title', $this->title);
	}

}


?>