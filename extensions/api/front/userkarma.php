<?php

use engine\system;
use engine\database;
use engine\property;
use engine\user;
use engine\extension;

class api_userkarma_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $make = system::getInstance()->get('make');
        switch($make) {
            case 'change':
                $this->changeKarma();
                break;
            case 'urllist':
                $this->urlList();
                break;
        }
    }

    private function urlList() {
        $response = array();
        $url = system::getInstance()->get('url');
        if($url == null)
            exit('error');
        if(!extension::getInstance()->getConfig('use_karma', 'user', extension::TYPE_COMPONENT, 'int'))
            exit('error');
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_karma WHERE url = ?");
        $stmt->bindParam(1, $url, \PDO::PARAM_STR);
        $stmt->execute();
        $resultAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // load in 1 query all user data
        $ids = system::getInstance()->extractFromMultyArray('from_id', $resultAll);
        user::getInstance()->listload($ids);
        foreach($resultAll as $result) {
            $response['karmainfo'][] = array(
                'from_id' => $result['from_id'],
                'type' => $result['type'],
                'from_name' => user::getInstance()->get('nick', $result['from_id'])
            );
        }
        echo json_encode($response);
    }

    private function changeKarma() {
        $type = (int)system::getInstance()->get('type');
        $target_id = (int)system::getInstance()->get('target');
        $url = system::getInstance()->get('url');
        if(!extension::getInstance()->getConfig('use_karma', 'user', extension::TYPE_COMPONENT, 'int'))
            exit('error');
        if(!system::getInstance()->prefixEquals($url, property::getInstance()->get('url')))
            exit('error');
        if($type != 1 && $type != 0)
            exit('error');
        if(!user::getInstance()->exists($target_id))
            exit('error');
        $caster_id = user::getInstance()->get('id');
        $caster_ip = system::getInstance()->getRealIp();
        if($target_id < 1 || $caster_id < 1 || $target_id == $caster_id)
            exit('error');
        // check in karma logs
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_karma WHERE (from_ip = ? AND to_id = ?) OR (from_id = ? AND to_id = ?) ORDER BY `date` DESC LIMIT 1");
        $stmt->bindParam(1, $caster_ip, \PDO::PARAM_STR);
        $stmt->bindParam(2, $target_id, \PDO::PARAM_INT);
        $stmt->bindParam(3, $caster_id, \PDO::PARAM_INT);
        $stmt->bindParam(4, $target_id, \PDO::PARAM_INT);
        $stmt->execute();

        if($check = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if($check['date'] > strtotime('-1 day'))
                exit('error');
        }

        $stmt = null;
        // check is past
        $date_now = time();
        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_karma (`from_id`, `to_id`, `type`, `date`, `from_ip`, `url`) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $caster_id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $target_id, \PDO::PARAM_INT);
        $stmt->bindParam(3, $type, \PDO::PARAM_INT);
        $stmt->bindParam(4, $date_now, \PDO::PARAM_INT);
        $stmt->bindParam(5, $caster_ip, \PDO::PARAM_STR);
        $stmt->bindParam(6, $url, \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;

        if($type == 1) {
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET karma = karma+1 WHERE id = ?");
            $stmt->bindParam(1, $target_id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
        } else {
            $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user_custom SET karma = karma-1 WHERE id = ?");
            $stmt->bindParam(1, $target_id, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
        }
        user::getInstance()->overload($target_id);
        $new_karma = user::getInstance()->get('karma', $target_id);
        if($new_karma > 0)
            echo "+";
        echo $new_karma;

        if($type == 1)
            echo "<i class=\"fa fa-arrow-up\"></i>";
        else
            echo "<i class=\"fa fa-arrow-down\"></i>";
    }
}