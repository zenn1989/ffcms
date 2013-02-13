<?php
/**
* класс управляющий языками отображения сайта
*/
class language
{

private $lang = array();

	function __construct()
	{
		global $constant;
		$file = $constant->root.'/language/'.$constant->lang.'.lang';
		if(file_exists($file))
		{
			$con = file_get_contents($file);
			$lang_array = explode("\n", $con);
			foreach($lang_array as $line)
			{
				// a<=>b is a min. example
				if(strlen($line) > 4)
				{
					list($tag,$value) = explode("<=>", $line);
					$this->lang[$tag] = $value;
				}	
			}
		}
	}
	public function set($data)
	{
		global $constant;
		foreach($this->lang as $tag=>$value)
		{
			$data = str_replace('{$lang::'.$tag.'}', $value, $data);
		}
		return $data;
	}

}
?>