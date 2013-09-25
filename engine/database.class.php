<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

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
            $this->con = @new PDO("mysql:host={$constant->db['host']};dbname={$constant->db['db']}", $constant->db['user'], $constant->db['pass'], array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false));
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