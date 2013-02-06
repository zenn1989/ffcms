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
}

?>