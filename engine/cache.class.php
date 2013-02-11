<?php

/**
* Класс кеширования страниц
*/
class cache
{

	private $noexist = false;

	public function check()
	{
		global $page,$constant;
		if($constant->debug_no_cache)
		{
			return false;
		}
		$way = $page->getPathway();
		// анализируем базовые нулевые пачвеи на список игнора кеша
		if(in_array($way[0], $page->getNoCache()))
		{
			return false;
		}
		// получаем путь к файлу кеша
		$file = $this->pathHash();
		if(file_exists($file))
		{
			$filetime = filemtime($file);
			// если файл пролежал меньше, чем указано в конфиге
			if((time() - $filetime) < $constant->cache_interval)
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	* Получение кеша. Обязательна проверка ДО использования данного параметра!
	*/	
	public function get()
	{
		// обязательна проверка cache::check до того, как будет выполнена функция get()!!!!
		return file_get_contents($this->pathHash());
	}
	
	/**
	* Сохранение страницы в кеш
	*/
	
	public function save($data)
	{
		global $page,$user;
		// не сохраняем для авторизованных пользователей
		if($user->getUserId() != 0)
		{
			return;
		}
		// Не сохраняем 404 ошибки как кеш
		if($this->noexist)
		{
			return;
		}
		$way = $page->getPathway();
		// если кеш запрещен - не сохраняем
		if(in_array($way[0], $page->getNoCache()))
		{
			return;
		}
		$place = $this->pathHash();
		file_put_contents($place, $data);	
	}
	
	/**
	* Хеш-функция, указывает на полный адрес файла в папке cache
	* Возможны коллизии, если $page->getStrPathway() > 32 символов, однако
	* данные колизии не критичны в данном случае.
	*/
	private function pathHash()
	{
		global $constant,$page;
		return $constant->root."/cache/".md5($page->getStrPathway());
	}
	
	public function setNoExist($boolean)
	{
		$this->noexist = $boolean;
	}


}
?>