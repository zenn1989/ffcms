<?php

/**
 * Стандартный класс для работы с базой данных
 */
class database
{
    private $con = null;
    private $queries = 0;

    function database()
    {
        global $constant;
        try {
            $this->con = new PDO("mysql:host={$constant->db['host']};dbname={$constant->db['db']}", $constant->db['user'], $constant->db['pass']);
            // отключаем эмуляцию, т.к. мы не фильтруем INPUT данные, ведь это умеет PDO
            // ставим жесткий указатель на UTF8
            $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->con->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            //exit("Database connection error " . $e);
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

    public function isDown()
    {
        return $this->con == null ? true : false;
    }
}


?>