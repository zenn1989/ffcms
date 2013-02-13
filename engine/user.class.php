<?php

/**
 * Класс отвечающий за пользовательские данные 
 */
class user
{
	private $userid = 0;
	private $token = null;
	private $usermail = null;
	private $userpassmd5 = null;
	
	function __construct()
	{
		$this->analiseToken();
	}
	
	/**
	* Анализ токена из кук.
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
			$query = "SELECT * FROM {$constant->db['prefix']}_user WHERE email = ? AND token = ?";
			$stmt = $database->con()->prepare($query);
			$stmt->bindParam(1, $email, PDO::PARAM_STR);
			$stmt->bindParam(2, $token, PDO::PARAM_STR, 32);
			$stmt->execute();
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetch();
				if((time() - $result['token_start']) < $constant->token_time)
				{
					$this->userid = $result['id'];
					$this->token = $token;
					$this->usermail = $email;
					$this->userpassmd5 = $result['pass'];
				}
			}
		}
	}
	
	/**
	* Возвращает ID пользователя. 0 = не авторизован
	*/
	public function getUserId()
	{
		return $this->userid;
	}
	
	/**
	* Возвращает email авторизованного пользователя
	* если не авторизован - null
	*/
	public function getUserMail()
	{
		return $this->usermail;
	}
    
}

?>