<?php
/**
* Компонент статических страниц
*/


/**
* регистрация области uri компонента
* Первый параметр - uri, второй - директория компонента
*/
$static_selfway = 'static';
$static_dirname = 'static';
$register = page::registerPathWay($static_selfway, $static_dirname);


if(!$register) { exit("Component $selfway => $dirname cannot be registered!"); }


/**
* Главный класс компонента. Имя = имя директории компонента.
*/
class com_static
{
	/**
	* Одноименный метод. Должен возвращать результат обработки.
	*/
	public function load()
	{
		return "This is welcome message!";
	}
}

?>