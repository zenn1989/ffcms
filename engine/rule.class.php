<?php 

/**
 * Класс реализующий правила и их обработку в шаблонах. Пример: {$if rule}some data{/$if}
 * @author zenn
 *
 */
class rule
{
	public $rule_data = array();
	private $init = false;
	
	// реализация нативных правил
	public function getInstance()
	{
		global $user;
		if(!$this->init)
		{
			if($user->get('id') > 0)
			{
				$this->rule_data['user.auth'] = true;
			}
			$this->init = true;
		}
		return $this;
	}
	
	/**
	 * Проверка условия.
	 * @param unknown_type $rule
	 */
	public function check($rule)
	{
		// возможен массив условий a && !a && b
		$rule_array = explode(" && ", $rule);
		$result_check = true;
		foreach($rule_array as $singleton)
		{
			if(substr($singleton, 0, 1) == "!")
			{
				$singleton = substr($singleton, 1);
				// если условие истино - то найдено исключение для !
				if($this->rule_data[$singleton])
				{
					$result_check = false;
				}
			}
			else
			{
				if(!$this->rule_data[$singleton])
				{
					$result_check = false;
				}
			}
		}
		return $result_check;
	}
	
	/**
	 * Добавление правил для расширений и пользовательских наработок.
	 * @param unknown_type $rule
	 * @param unknown_type $value
	 */
	public function add($rule, $value)
	{
		if(!array_key_exists($rule, $this->rule_data))
		{
			$this->rule_data[$rule] = $value;
		}
	}
}


?>