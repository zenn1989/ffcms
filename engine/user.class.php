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
		$userid = (int)$_COOKIE['userid'];
		if(strlen($token) == 32 || $userid > 0)
		{
			$query = "SELECT * FROM {$constant->db['prefix']}_user WHERE id = ? AND token = ?";
			$stmt = $database->con()->prepare($query);
			$stmt->bindParam(1, $userid, PDO::PARAM_INT);
			$stmt->bindParam(2, $token, PDO::PARAM_STR, 32);
			$stmt->execute();
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetch();
				if((time() - $result['token_start']) < $constant->token_time)
				{
					$this->userid = $userid;
					$this->token = $token;
					$this->usermail = $result['email'];
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
    
}

?>