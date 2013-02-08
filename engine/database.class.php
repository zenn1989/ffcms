<?php

/**
 * Стандартный класс для работы с базой данных
 */
class database
{
    public $con = null;
	
	function __construct()
	{
		global $constant;
		try
		{
			$this->con = new PDO("mysql:host={$constant->db['host']};dbname={$constant->db['db']}", $constant->db['user'], $constant->db['pass']);
		}
		catch(Exception $e)
		{
			exit("Database connection error".$e);
		}
	}
	
	function __destruct()
	{
		$this->con = null;
	}
}


?>