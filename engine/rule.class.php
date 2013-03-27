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
	
	// реализация правил
	public function getInstance()
	{
		global $user;
		if(!$this->init)
		{
			if($user->get('id') != NULL)
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
		// а не обратное ли это правило?
		if(substr($rule, 0, 1) == "!")
		{
			// Отрезаем !
			$rule = substr($rule, 1);
			return !$this->rule_data[$rule];
		}
		return $this->rule_data[$rule];
	}
}


?>