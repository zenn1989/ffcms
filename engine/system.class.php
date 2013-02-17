<?php

class system
{
	
	/**
	* Boolean функция, отвечающая true если $what обнаружено в $where
	*/
	public function contains($what, $where)
	{
		$answer = false;
		if(strpos($where, $what)!==false)
		{
			$answer = true;
		}
		return $answer;
	}
	
	/**
	* Удаляет расширение у $var (indexxxx.html => index, vasya.exe => vasya)
	* Не спасет от идиотизма вида index.html.html.ht.html.ml но нам это и не нужно.
	*/
	public function noextention($var)
	{
		// режем
		$split = explode(".", $var);
		// крутим цикл, исключаем последний элемент
		if(sizeof($split) > 1)
		{
			$return = null;
			for($i=0;$i<sizeof($split);$i++)
			{
				$return .= $split[$i];
			}
			return $return;
		}
		return $var;
	}
	
	
	/**
	* Безопасный html. Применять к входящим данным от пользователя.
	*/
	public function safeHtml($data, $allowed = '')
	{
		$unsafe_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		return preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $unsafe_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($data, $allowed));
	}
	
	/**
	* Удаление html тегов
	*/
	public function nohtml($data)
	{
		return strip_tags($data);
	}
	
	/**
	* Псевдо-случайная A-Za-z-9 строка с заданной длиной
	* Алгоритм достаточно устойчив к бруту, если его длина не менее 16 символов
	* Однако, для токенов или подобных алгоритмов, рекомендуем функцию md5random()
	*/
	public function randomString($length)
	{
		$ret = 97;
		$out = null;
		for($i=0;$i<$length;$i++)
		{
			$offset = rand(0,15);
			$char = chr($ret+$offset);
			$posibility = rand(0,2);
			if($posibility == 0)
			{
				// 33% - подмешиваем случайное число
				$out .= rand(0,9);
			}
			elseif($posibility == 1)
			{
				// 33% - поднимаем в верхний регистр из offset+ret
				$out .= strtoupper($char);
			}
			else
			{
				$out .= $char;
			}
		}
		return $out;	
	}
	
	/**
	* Случайный md5-хеш на основе функции randomString
	* $min и $max - показатели для выборки случайного размера исходной строки
	*/
	public function md5random($min = 16, $max = 20)
	{
		return md5($this->randomString(rand($min,$max)));
	}
	
	/**
	* Случайная величина отталкиваясь от уникального значения $data
	*/
	public function randomWithUnique($data, $min = 16, $max = 30)
	{
		$offset_min = $min-strlen($data);
		$offset_max = $max-strlen($data);
		if($offset_max < 0)
		{
			return $this->md5random();
		}
		elseif($offset_min < 0)
		{
			$data .= $this->randomString(rand(1, $offset_max));
		}
		else
		{
			$data .= $this->randomString(rand($offset_min, $offset_max));
		}
		return md5($data);
	}
	
	/**
	* Перенаправление пользователей, обязателен корень /
	*/
	public function redirect($uri = null)
	{
		global $constant;
		header("Location: {$constant->url}{$uri}"); 
	}
	
}

?>