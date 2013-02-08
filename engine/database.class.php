<?php

/**
 * Стандартный класс для работы с базой данных
 */
class database
{
    private $con = null;
	private $queries = 0;
	
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
	
	public function con()
	{
		$this->queries++;
		return $this->con;
	}
	
	public function totalQueryCount()
	{
		return $this->queries;
	}
	
	function __destruct()
	{
		$this->con = null;
	}
}


?>