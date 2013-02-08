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
}

?>