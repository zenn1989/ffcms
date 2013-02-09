<?php

// регистрируем компонент
if(!page::registerPathWay('admin', 'admin')) { exit("Component $selfway => $dirname cannot be registered!"); }
// запрещаем кеширование компонента
page::setNoCache('admin');

class com_admin
{
	public function load()
	{
		global $page;
		// работаем с заголовком
		$path = $page->getPathway();
		switch($path[1])
		{
			case "components":
			return $this->componentsMain();
			break;
			default:
			break;
		}
	}
	
	private function componentsMain()
	{
		return "This is example of components editions";
	}
}

?>