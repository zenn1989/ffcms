<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class permission extends singleton {

    protected $all_permissions = array();
    protected $full_access_data = array();

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
     * Check access to part of admin interface
     * @param string $object
     * @param string $action
     * @param string $make
     * @param int $target_id
     * @return bool
     */
    public function haveAdmin($object, $action, $make, $target_id = 0) {
        $str_to_compare = 'admin/';
        if(system::getInstance()->length($object) > 0)
            $str_to_compare .= $object;
        else
            $str_to_compare .= 'main';
        if(system::getInstance()->length($action) > 0)
            $str_to_compare .= '/' . $action;
        if(system::getInstance()->length($make) > 0)
            $str_to_compare .= '/' . $make;
        $userdata = user::getInstance()->get('permissions', $target_id);
        $have_rights = system::getInstance()->altexplode(';', $userdata);

        return in_array($str_to_compare, $have_rights);
    }

    /**
     * Get admin interface all available permissions
     * @return array
     */
    public function getAdminPermissions() {
        $ext = extension::getInstance()->getAllParams();
        $result = array();
        $general_rights = admin::getInstance()->getDefaultAccessRights();
        foreach($general_rights as $right) {
            $result[] = $right;
        }
        foreach($ext as $ext_data) {
            foreach($ext_data as $ext_item) {
                $ext_name = $ext_item['dir'];
                $ext_type = $ext_item['type'];
                $ext_enabled = $ext_item['enabled'] == 1;
                $pathway = root . '/extensions/' . $ext_type . '/' . $ext_name . '/back.php';
                if($ext_enabled && file_exists($pathway)) {
                    $cname = $ext_type . '_' . $ext_name . '_back';
                    require_once($pathway);
                    if(class_exists($cname)) {
                        $init = new $cname;
                        if(method_exists($init, 'getInstance') && method_exists($init, 'accessData')) {
                            $data = $init::getInstance()->accessData();
                            foreach($data as $single) {
                                $result[] = $single;
                            }
                            $init = null;
                        }
                    }
                }
            }
        }
        return $result;
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
                if(!in_array($permission, $this->all_permissions) && !system::getInstance()->prefixEquals($permission, 'admin/')) {
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