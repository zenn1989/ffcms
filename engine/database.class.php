<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class database extends singleton {
    /**
     * @var \PDO
     */
    protected $link = null;
    protected $count = 0;

    public function init() {
        if(is_null($this->link)) {
            try {
                $this->link = @new \PDO("mysql:host=".property::getInstance()->get('db_host').";dbname=".property::getInstance()->get('db_name')."",
                                        property::getInstance()->get('db_user'), property::getInstance()->get('db_pass'),
                                        array(
                                            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                                            \PDO::ATTR_EMULATE_PREPARES => false,
                                            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                                            \PDO::ATTR_PERSISTENT => false)
                                        );
            } catch(\PDOException $e) {
                logger::getInstance()->log(logger::LEVEL_ERR, "Database is down! Check configuration and database server uplink! Log: " . $e->getMessage());
                exit(language::getInstance()->get('database_down_desc') . " " . property::getInstance()->get('mail_from'));
            }
        }
    }

    /**
     * Return link to PDO connection on system database
     * @return \PDO
     */
    public function con() {
        $this->count++;
        return $this->link;
    }

    /**
     * Return query count maked to database. Function for debug.
     * @return int
     */
    public function getQueryCount() {
        return $this->count;
    }

    function __destruct() {
        $this->link = null;
    }

    /**
     * Check is database down now
     * @return bool
     */
    public function isDown() {
        return is_null($this->link);
    }

}