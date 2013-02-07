<?php

/**
 * Стандартный клас для работы элементами страницы
 */
class page
{
	private $pathway = array();

	function __construct()
	{
		$this->rawuri();
	}
	
	public function getPathway()
	{
		return $this->pathway;
	}
	
	/**
	* Разбивка запроса от пользователя на массив.
	* Пример: /novosti/obshestvo/segodnya-sluchilos-prestuplenie.html приймет вид:
	* array(0 => 'novosti', 1 => 'obshestvo', 2 => 'segodnya-sluchilos-prestuplenie.html')
	*/
	private function rawuri()
	{
		$split = explode("/", $_SERVER['REQUEST_URI']);
		foreach($split as $values)
		{
			if($values != null)
			{
				$this->pathway[] = $values;
			}
		}
	}
	
	
	
	/**
	* Возвращение массива позиции. Пример.
	*/
	public function getHeader()
	{
		return array('<p>', 'This is test', '</p>');
	}
    
}


?>