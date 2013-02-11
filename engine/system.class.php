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
}

?>