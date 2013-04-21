<?php

/**
 * Динамические пользовательские многоуровневые меню управления и навигации на сайте. Видимая часть для пользователя.
 */

class mod_menu_manager_front implements mod_front
{
	// загрузка, метод after, по глобальному шаблону
	public function after()
	{
		return $this->loadSql();
	}

	public function before() {
	}

	// выбираем из sql нужные даные
	// имена шаблонов, вложенный массив с неограниченным количеством уровней
	private function loadSql()
	{
		global $database,$constant;
		$stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_mod_menu_manager WHERE active = 1");
		while($rs = $stmt->fetch())
		{
			$tpl_makets = array($rs['tpl_open_tag'], $rs['tpl_close_tag'], $rs['tpl_sub_open_tag'], $rs['tpl_sub_close_tag'], $rs['tpl_list_tag']);
			$this->prepareMenu($rs['tag_name'], $rs['constuct'], $tpl_makets);
		}
	}
	// Подготавливаем менюшку к дальнейшим операциям, разбираем serialize, после парсинга выставляем в каркасе main по позиции
	private function prepareMenu($position, $array_serialized, $tpl)
	{
		global $template;
		$phparray = unserialize($array_serialized);
		$string_menu = $this->preparePharse($phparray, $tpl);
		$template->set($position, $string_menu);
	}
	// выгружаем шаблоны, формируем массив нужных шаблонов для след. действия парсинга
	private function preparePharse($array, $tpl)
	{
		global $template;
		$res = $template->tplget($tpl[0], 'modules/mod_menu_manager/');
		// 0 = sub_open_tag, 1 = $sub_close_tag, 2 = list
		$prepare_tpl = array($template->tplget($tpl[2], 'modules/mod_menu_manager/'), $template->tplget($tpl[3], 'modules/mod_menu_manager/'), $template->tplget($tpl[4], 'modules/mod_menu_manager/'));
		$res .= $this->pharse($array, $prepare_tpl);
		$res .= $template->tplget($tpl[1], 'modules/mod_menu_manager/');
		return $res;
	}

	/**
	 * Рекурсивный метод парсинга массива в Ul>li список. Осторожно, злой код!
	 */
	private function pharse($array, $tpl, $sub = false)
	{
		global $template;
		$result = null;
		if($sub)
		{
			$result .= $template->assign('menu_header', $array[0], $tpl[0]);
		}
		foreach($array as $key=>$values)
		{
			if(is_array($values))
			{
				$result .= $this->pharse($values, $tpl, true);
				$result .= $tpl[1];
			}
			else
			{
				if($key != 0)
				{
					$result .= $template->assign('menu_item', $values, $tpl[2]);
				}
			}
		}
		return $result;
	}


}


?>