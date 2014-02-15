<?php

namespace engine;
use PDO;
use PDOException;

class database extends singleton {
    protected static $instance = null;
    /** @var \PDO */
    protected static $link = null;
    protected static $count = 0;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            try {
                self::$link = @new \PDO("mysql:host=".property::getInstance()->get('db_host').";dbname=".property::getInstance()->get('db_name')."", property::getInstance()->get('db_user'), property::getInstance()->get('db_pass'), array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false));
            } catch(\PDOException $e) {
                logger::getInstance()->log(logger::LEVEL_ERR, "Database is down! Check configuration and database server uplink! Log: " . $e->getMessage());
                exit(template::getInstance()->twigRender('database_down.tpl', array('admin_mail' => property::getInstance()->get('mail_from'))));
            }
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return \PDO
     */
    public function con() {
        self::$count++;
        return self::$link;
    }

    /**
     * Return query count maked to database. Function for debug.
     * @return int
     */
    public function getQueryCount() {
        return self::$count;
    }

    function __destruct() {
        self::$link = null;
    }

    public function isDown() {
        return is_null(self::$link);
    }

}