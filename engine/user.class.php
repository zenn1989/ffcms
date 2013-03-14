<?php

/**
 * Класс отвечающий за пользовательские данные
 */
class user
{
	private $userpamar = array();
	private $userid = 0;
	private $username = null;
	private $token = null;
	private $usermail = null;
	private $userpassmd5 = null;
	private $accesslevel = 0;
	private $access_to_admin = false;

	function __construct()
	{
		$this->analiseToken();
	}

	/**
	 * Анализ пользовательских данных, установка параметров если пользователь авторизован.
	 */
	private function analiseToken()
	{
		global $database, $constant;
		// необходимая пара для анализа авторизован ли пользователь
		$token = $_COOKIE['token'];
		$email = $_COOKIE['email'];
		// данные удовлетворяют шаблон
		if(strlen($token) == 32 && filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$query = "SELECT * FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_access_level b WHERE a.email = ? AND a.token = ? AND a.aprove = 0 AND a.access_level = b.group_id";
			$stmt = $database->con()->prepare($query);
			$stmt->bindParam(1, $email, PDO::PARAM_STR);
			$stmt->bindParam(2, $token, PDO::PARAM_STR, 32);
			$stmt->execute();
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if((time() - $result[0]['token_start']) < $constant->token_time)
				{
					foreach($result[0] as $column_index=>$column_data)
					{
						//echo $column."=>".$data."<br />";
						$this->userpamar[$column_index] = $column_data;
					}
				}
			}
		}
	}

	/**
	 * Получение определенных пользовательских данных - аналогично названию в таблицах _user / user_access_level
	 * @param String $param
	 * @return multitype: String or INT or NULL
	 */
	public function get($param)
	{
		return $this->userpamar[$param];
	}
}

?>