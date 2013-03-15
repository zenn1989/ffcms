<?php
/**
* Компонент статических страниц
*/


/**
* регистрация области uri компонента
* Первый параметр - uri, второй - директория компонента
*/
if(!extension::registerPathWay('static', 'static')) { exit("Component static cannot be registered!"); }

/**
* Главный класс компонента. Имя = com_ + имя директории компонента.
*/
class com_static
{
	/**
	* Одноименный метод. Должен возвращать результат обработки.
	*/
	public function load()
	{
		global $page,$template,$system;
		$way = $page->getPathway();
		// генерируем pathway для sql запроса из массива
		$sqllink = null;
		for($i=1;$i<=count($way)-1;$i++)
		{
			$sqllink .= $way[$i];
			if($i!=count($way)-1)
			{
				$sqllink .= "/";
			}
		}
		// либо запрос пуст, либо пользователь наркоман
		if($sqllink == null)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		$page->setContentPosition('body', $this->loadSinglePage($sqllink));

	}
	
	/**
	* Отображение статической страницы. Первый компонент (:
	*/
	private function loadSinglePage($pathway)
	{
		global $database,$constant,$template;
		$query = "SELECT * FROM {$constant->db['prefix']}_com_static WHERE pathway = ?";
		$stmt = $database->con()->prepare($query);
		$stmt->bindParam(1, $pathway, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		if($stmt->rowCount() != 1)
		{
			return $template->compile404();
		}
		$com_theme = $template->tplget("com_static", "components/");
		return $template->assign(array('title', 'text', 'date'), array($result['title'], $result['text'], $result['date']), $com_theme);
		
	}
	
}

?>