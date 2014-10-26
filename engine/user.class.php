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

    protected $userdata = array();
    protected $userindex = 0;
    protected $karmadata = array();

    public function init() {
        $token = isset($_SESSION['token']) ? $_SESSION['token'] : $_COOKIE['token'];
        $personal_id = isset($_SESSION['person']) ? $_SESSION['person'] : $_COOKIE['person'];
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
                    $this->userindex = $result[0]['id'];
                    foreach ($result[0] as $column_index => $column_data) {
                        $this->userdata[$this->userindex][$column_index] = $column_data;
                    }
                    // set template variables
                    template::getInstance()->set(template::TYPE_USER, 'id', $this->userindex);
                    template::getInstance()->set(template::TYPE_USER, 'name', $this->userdata[$this->userindex]['nick']);
                    template::getInstance()->set(template::TYPE_USER, 'admin', permission::getInstance()->have('global/owner'));
                    template::getInstance()->set(template::TYPE_USER, 'admin_panel', permission::getInstance()->have('admin/main'));
                    template::getInstance()->set(template::TYPE_USER, 'news_add', extension::getInstance()->getConfig('enable_useradd', 'news', extension::TYPE_COMPONENT, 'bol'));
                    template::getInstance()->set(template::TYPE_USER, 'balance', $this->userdata[$this->userindex]['balance']);
                }
            }
        }
    }

    private function set($userid, $overload = false) {
        if(!$overload && array_key_exists($userid, $this->userdata))
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
            $this->userdata[$userid][$key] = $value;
        }
    }

    public function overload($userid) {
        $this->set($userid, true);
    }

    public function get($param, $userid = 0) {
        // current user, is always loaded on getInstance()
        if($userid === 0) {
            return $this->userdata[$this->userindex][$param];
        }
        // custom user, make load
        $this->set($userid);
        return $this->userdata[$userid][$param];
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
     * @param string|array $idlist
     */
    public function listload($idlist) {
        $list_array = system::getInstance()->removeNullFrontIntList($idlist); // array
        if(sizeof($list_array) < 2)
            return;
        $idlist = system::getInstance()->altimplode(',', $list_array); // string
        $query = "SELECT * FROM
            ".property::getInstance()->get('db_prefix')."_user a,
            ".property::getInstance()->get('db_prefix')."_user_access_level b,
            ".property::getInstance()->get('db_prefix')."_user_custom c
            WHERE a.id in ($idlist) AND a.aprove = 0 AND a.access_level = b.group_id AND a.id = c.id";
        $stmt = database::getInstance()->con()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $item) {
            foreach($item as $param => $data) {
                $this->userdata[$item['id']][$param] = $data;
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

    /**
     * Save user log into special table
     * @param int|null $owner
     * @param string $type
     * @param array $params
     * @param string $message
     * @param null $time
     * @return bool|string
     */
    public function putLog($owner = null, $type, $params, $message, $time = null) {
        if($owner < 1)
            $owner = $this->get('id');
        if($time == null)
            $time = time();

        if(!is_array($params))
            return false;
        if($owner == 0)
            return false;
        $save_params = serialize($params);

        $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_user_log (`owner`, `type`, `params`, `message`, `time`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $owner, \PDO::PARAM_INT);
        $stmt->bindParam(2, $type, \PDO::PARAM_STR);
        $stmt->bindParam(3, $save_params, \PDO::PARAM_STR);
        $stmt->bindParam(4, $message, \PDO::PARAM_STR);
        $stmt->bindParam(5, $time, \PDO::PARAM_INT);
        $stmt->execute();

        $transaction_id = database::getInstance()->con()->lastInsertId();
        $stmt = null;
        return $transaction_id;

    }

    /**
     * Get user logs from db from time or by type with limit lines
     * @param int $owner
     * @param null $type
     * @param null $time
     * @param int $line_count
     * @return array|bool
     */
    public function getLog($owner = 0, $type = null, $time = null, $line_count = 1) {
        if($type == null && $time == null)
            return false;
        if($owner == 0)
            $owner = $this->get('id');
        if($time == null) { // get by type
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_log WHERE `owner` = ? AND `type` = ? ORDER BY `time` LIMIT 0,?");
            $stmt->bindParam(1, $owner, \PDO::PARAM_INT);
            $stmt->bindParam(2, $type, \PDO::PARAM_INT);
            $stmt->bindParam(3, $line_count, \PDO::PARAM_INT);
            $stmt->execute();
            $resultAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt = null;
            return $resultAll;
        } else { // get by time
            $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_log WHERE `owner` = ? AND `time` >= ? ORDER BY `time` LIMIT 0,?");
            $stmt->bindParam(1, $owner, \PDO::PARAM_INT);
            $stmt->bindParam(2, $time, \PDO::PARAM_INT);
            $stmt->bindParam(3, $line_count, \PDO::PARAM_INT);
            $stmt->execute();
            $resultAll = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt = null;
            return $resultAll;
        }
    }

    /**
     * Add money to user balance.
     * @param int $user_id
     * @param double $amount
     * @return bool
     */
    public function addBalance($user_id = 0, $amount) {
        if($user_id == 0)
            $user_id = $this->get('id');
        $amount = (float)$amount;
        if($user_id < 1 || $amount <= 0)
            return false;

        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET balance = balance + ? WHERE id = ?");
        $stmt->bindParam(1, $amount, \PDO::PARAM_STR);
        $stmt->bindParam(2, $user_id, \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
        return true;
    }

    /**
     * Reduce money from user balance (outgo)
     * @param int $user_id
     * @param double $amount
     * @return bool
     */
    public function flowBalance($user_id = 0, $amount) {
        if($user_id == 0)
            $user_id = $this->get('id');
        $amount = (float)$amount;
        if($user_id < 1 || $amount <= 0)
            return false;

        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_user SET balance = balance - ? WHERE id = ?");
        $stmt->bindParam(1, $amount, \PDO::PARAM_STR);
        $stmt->bindParam(2, $user_id, \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
        return true;
    }

    /**
     * Check if $user_id can change karma rating for $target_id
     * @param $target_id
     * @param int $user_id
     * @return bool
     */
    public function canKarmaChange($target_id, $user_id = 0) {
        if($user_id == 0)
            $user_id = $this->get('id');
        if($user_id == $target_id || $user_id == 0)
            return false;
        if(!isset($this->karmadata[$user_id])) {
            $check_date = strtotime('-1 day');
            $stmt = database::getInstance()->con()->prepare("SELECT `to_id` FROM ".property::getInstance()->get('db_prefix')."_user_karma WHERE `from_id` = ? AND `date` >= ?");
            $stmt->bindParam(1, $user_id, \PDO::PARAM_STR);
            $stmt->bindParam(2, $check_date, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt = null;

            foreach($result as $row) {
                $this->karmadata[$user_id][] = $row['to_id'];
            }
        }
        return !in_array($target_id, $this->karmadata[$user_id]);
    }

}