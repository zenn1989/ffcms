<?php

/**
 * Класс отвечающий за пользовательские данные
 */
class user
{
	private $userpamar = array();

	function __construct()
	{
		$this->set();
	}

	/**
	 * Анализ пользовательских данных, установка параметров если пользователь авторизован.
	 */
	private function set()
	{
		global $database, $constant,$system;
		// необходимая пара для анализа авторизован ли пользователь
		// pesonal id может быть как логином так и почтой
		$token = $_COOKIE['token'];
		$personal_id = $_COOKIE['person'];
		// данные удовлетворяют шаблон
		if(strlen($token) == 32 && (filter_var($personal_id, FILTER_VALIDATE_EMAIL) || (strlen($personal_id) > 0 && $system->isLatinOrNumeric($personal_id))))
		{
			$query = "SELECT * FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_access_level b WHERE (a.email = ? OR a.login = ?) AND a.token = ? AND a.aprove = 0 AND a.access_level = b.group_id";
			$stmt = $database->con()->prepare($query);
			$stmt->bindParam(1, $personal_id, PDO::PARAM_STR);
			$stmt->bindParam(2, $personal_id, PDO::PARAM_STR);
			$stmt->bindParam(3, $token, PDO::PARAM_STR, 32);
			$stmt->execute();
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if((time() - $result[0]['token_start']) < $constant->token_time)
				{
					foreach($result[0] as $column_index=>$column_data)
					{
						$this->userpamar[$column_index] = $column_data;
					}
				}
			}
		}
	}

	/**
	 * Получение определенных пользовательских данных - аналогично названию в таблицах user / user_access_level
	 * @param String $param
	 * @return multitype: String or INT or NULL
	 */
	public function get($param)
	{
		return $this->userpamar[$param];
	}
}

?>