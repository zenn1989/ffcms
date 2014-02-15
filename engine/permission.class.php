<?php

namespace engine;

use \PDO;

class permission extends singleton {
    protected static $instance = null;
    protected $all_permissions = array();
    protected $full_access_data = array();

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Did current user have a $permission ?
     * @param string('global/read', 'global/write', 'global/owner', 'comment/add', 'comment/edit', 'comment/delete') $permission
     * @param int $target_id
     * @return bool
     */
    public function have($permission, $target_id = 0) {
        if($target_id < 1) // selfdata
            $target_id = user::getInstance()->get('id');
        $userdata = user::getInstance()->get('permissions', $target_id);
        if(system::getInstance()->length($userdata) < 1) // no perms?
            return false;
        $perm_array = system::getInstance()->altexplode(';', $userdata);
        if(sizeof($perm_array) > 0 && (in_array($permission, $perm_array) || in_array('global/owner', $perm_array))) {
            return true;
        }
        return false;
    }

    /**
     * Return array of all available permissions in datatable user_access_level. Ex: array['global/read', 'global/write', 'global/owner' ... , 'etc']
     * @return array
     */
    public function getAllPermissions()
    {
        $this->loadAllData(); // get data from db
        foreach($this->full_access_data as $row) { // even row
            $permission_array = system::getInstance()->altexplode(';', $row['permissions']); // row permissions
            foreach($permission_array as $permission) { // single permission
                if(!in_array($permission, $this->all_permissions)) {
                    $this->all_permissions[] = $permission; // add
                }
            }
        }
        return $this->all_permissions;
    }

    /**
     * Return all group data like db table as array
     * @return array
     */
    public function getAllData() {
        $this->loadAllData();
        return $this->full_access_data;
    }

    private function loadAllData() {
        if(sizeof($this->full_access_data) < 1) {
            $query = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_user_access_level");
            $this->full_access_data = $query->fetchAll(\PDO::FETCH_ASSOC);
        }
    }


}