<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class user extends singleton {
    protected static $instance = null;
    protected static $userdata = array();
    protected static $userindex = 0;

    /**
     * @return user
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::preload();
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected static function preload() {
        $token = $_COOKIE['token'];
        $personal_id = $_COOKIE['person'];
        $user_ip = system::getInstance()->getRealIp();
        // data 1st raw check before sql is used
        if (strlen($token) == 32 && (filter_var($personal_id, FILTER_VALIDATE_EMAIL) || (strlen($personal_id) > 0 && system::getInstance()->isLatinOrNumeric($personal_id)))) {
            $query = "SELECT * FROM
            ".property::getInstance()->get('db_prefix')."_user a,
            ".property::getInstance()->get('db_prefix')."_user_access_level b,
            ".property::getInstance()->get('db_prefix')."_user_custom c
            WHERE (a.email = ? OR a.login = ?) AND a.token = ? AND a.token_ip = ? AND a.aprove = 0 AND a.access_level = b.group_id AND a.id = c.id";
            $stmt = database::getInstance()->con()->prepare($query);
            $stmt->bindParam(1, $personal_id, \PDO::PARAM_STR);
            $stmt->bindParam(2, $personal_id, \PDO::PARAM_STR);
            $stmt->bindParam(3, $token, \PDO::PARAM_STR, 32);
            $stmt->bindParam(4, $user_ip, \PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $stmt = null;
                if ((time() - $result[0]['token_start']) < property::getInstance()->get('token_time')) {
                    self::$userindex = $result[0]['id'];
                    foreach ($result[0] as $column_index => $column_data) {
                        self::$userdata[self::$userindex][$column_index] = $column_data;
                    }
                }
            }
        }
    }

    private function set($userid, $overload = false) {
        if(!$overload && array_key_exists($userid, self::$userdata))
            return;
        if($userid < 1)
            return;
        $query = "SELECT * FROM
            ".property::getInstance()->get('db_prefix')."_user a,
            ".property::getInstance()->get('db_prefix')."_user_access_level b,
            ".property::getInstance()->get('db_prefix')."_user_custom c
            WHERE a.id = ? AND a.aprove = 0 AND a.access_level = b.group_id AND a.id = c.id";
        $stmt = database::getInstance()->con()->prepare($query);
        $stmt->bindParam(1, $userid, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() < 1)
            return;
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $key=>$value) {
            self::$userdata[$userid][$key] = $value;
        }
    }

    public function overload($userid) {
        $this->set($userid, true);
    }

    public function get($param, $userid = 0) {
        // current user, is always loaded on getInstance()
        if($userid === 0) {
            return self::$userdata[self::$userindex][$param];
        }
        // custom user, make load
        $this->set($userid);
        return self::$userdata[$userid][$param];
    }

    /**
     * Check user exist using ID
     * @param $userid
     * @return bool
     */
    public function exists($userid)
    {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user WHERE id = ?");
        $stmt->bindParam(1, $userid, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result[0] > 0 ? true : false;
    }

    /**
     * Load user data in memory from list $idlist (array or string list like 1,5,7,8)
     * @param $idlist
     */
    public function listload($idlist) {
        if (is_array($idlist)) {
            $idlist = system::getInstance()->altimplode(',', $idlist);
        }
        if (!system::getInstance()->isIntList($idlist) || strlen($idlist) < 1) {
            return;
        }
        $query = "SELECT * FROM
            ".property::getInstance()->get('db_prefix')."_user a,
            ".property::getInstance()->get('db_prefix')."_user_access_level b,
            ".property::getInstance()->get('db_prefix')."_user_custom c
            WHERE a.id in ($idlist) AND a.aprove = 0 AND a.access_level = b.group_id AND a.id = c.id";
        $query = database::getInstance()->con()->query($query);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        foreach($result as $item) {
            foreach($item as $param => $data) {
                self::$userdata[$item['id']][$param] = $data;
            }
        }
    }

    /**
     * Make & check user avatar is exist and return full image path without domain
     * @param string('small, 'medium', 'big') $type
     * @param int $userid
     * @return string
     */
    public function buildAvatar($type = "small", $userid)
    {
        $filepath = root . "/upload/user/avatar/$type/avatar_$userid.jpg";
        if (!file_exists($filepath)) {
            return 'resource/cmscontent/noavatar_'.$type.'.jpg';
        }
        // dynamic adding file make time
        $filetime = filemtime($filepath);
        $result = "upload/user/avatar/$type/avatar_$userid.jpg?mtime=".$filetime;
        return $result;
    }

    /**
     * Check is $mail exists in db
     * @param $mail
     * @return bool
     */
    public function mailIsExists($mail)
    {
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user WHERE email = ?");
        $stmt->bindParam(1, $mail, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result[0] > 0;
    }

    /**
     * Check $login is exist
     * @param $login
     * @return bool
     */
    public function loginIsExists($login)
    {
        if (strlen($login) < 3 || strlen($login) > 64) {
            return true;
        }
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_user WHERE login = ?");
        $stmt->bindParam(1, $login, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result[0] > 0;
    }

    /**
     * Get user id by email
     * @param $email
     * @return null|string
     */
    public function getIdByEmail($email) {
        $stmt = database::getInstance()->con()->prepare("SELECT id FROM ".property::getInstance()->get('db_prefix')."_user WHERE email = ?");
        $stmt->bindParam(1, $email, \PDO::PARAM_STR);
        $stmt->execute();
        if($rset = $stmt->fetch(\PDO::FETCH_ASSOC))
            return $rset['id'];
        return null;
    }

}