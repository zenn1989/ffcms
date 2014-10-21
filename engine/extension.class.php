<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class extension extends singleton {

    protected $callobjects = array();
    protected $extconfigs = array();

    const TYPE_MODULE = 'modules';
    const TYPE_COMPONENT = 'components';
    const TYPE_HOOK = 'hooks';

    /**
     * Check is URI pathway is always used. Example: extension::getInstance()->foundRoute('static') - check if /static/* way is used.
     * @param string $way
     * @return bool
     */
    public function foundRoute($way) {
        if(array_key_exists($way, $this->extconfigs['components']) && $way != null && !system::getInstance()->prefixEquals($way, '.') && $this->extconfigs['components'][$way]['enabled'] == 1)
            return true;
        return false;
    }

    /**
     * Call to extension class functions from remote class. Ex: extension::getInstance()->call(extension::TYPE_COMPONENT, 'static')->display();
     * @param string $type
     * @param string $object
     * @param boolean $is_back
     * @return mixed
     */
    public function call($type, $object, $is_back = false) {
        if(!is_object($this->callobjects[$type][$object])) {
            if(array_key_exists($type, $this->extconfigs) && array_key_exists($object, $this->extconfigs[$type])) {
                $file = root . '/extensions/' . $type . '/' . $object;
                if($is_back)
                    $file .= '/back.php';
                else
                    $file .= '/front.php';
                if(file_exists($file)) {
                    @require_once($file);
                    $cname = $type . '_' . $object;
                    if($is_back)
                        $cname .= '_back';
                    else
                        $cname .= '_front';
                    if(class_exists($cname)) {
                        $init = @new $cname;
                        if(method_exists($cname, 'getInstance')) {
                            $instance = @$init::getInstance();
                            if(is_object($instance)) {
                                $this->callobjects[$type][$object][$is_back ? 'back' : 'front'] = $instance;
                            } else {
                                logger::getInstance()->log(logger::LEVEL_WARN, 'Method getInstance() dosnt return object link for self(return $this) in file '.$file);
                            }
                        } else {
                            logger::getInstance()->log(logger::LEVEL_WARN, 'Method getInstance() not founded in file '.$file);
                        }
                    } else {
                        logger::getInstance()->log(logger::LEVEL_WARN, "Class ".$cname." not found in file ".$file);
                    }
                } else {
                    logger::getInstance()->log(logger::LEVEL_ERR, "File of extensions dosnt exists: ".$file);
                }
            }
        }
        return $this->callobjects[$type][$object][$is_back ? 'back' : 'front'];
    }

    public function loadModules() {
        foreach($this->extconfigs[self::TYPE_MODULE] as $mod_data) {
            if($mod_data['enabled'] == 1) { // if module is enabled
                // check is module work on this pathway.
                $work_on_this_path = false;
                if ($mod_data['path_choice'] == 1) {
                    // 	 { $this->stringPathway: component/aaa/ddd.html
                    //	<   											=> ok
                    //	 { module_rule: component/*
                    $allowed_array = explode(';', $mod_data['path_allow']);
                    foreach ($allowed_array as $allowed) {
                        // dont change it on false (can deny before excepted data)
                        $canwork = router::getInstance()->isRightWayRule($allowed);
                        if ($canwork) {
                            $work_on_this_path = true;
                        }
                    }
                } // list of deny ways
                else {
                    $find_deny = false;
                    $deny_array = explode(';', $mod_data['path_deny']);
                    foreach ($deny_array as $deny) {
                        if (router::getInstance()->isRightWayRule($deny)) {
                            $find_deny = true;
                        }
                    }
                    $work_on_this_path = !$find_deny;
                }
               // if module is working on this pathway URI - load it!
                if ($work_on_this_path) {
                    $object = $this->call(self::TYPE_MODULE, $mod_data['dir']);
                    if(is_object($object) && method_exists($object, 'make'))
                        $object->make();
                }
            }
        }
    }

    public function loadHooks() {
        foreach($this->extconfigs[self::TYPE_HOOK] as $hook_data) {
            if($hook_data['enabled'] == 1) {
                $callback = $this->call(self::TYPE_HOOK, $hook_data['dir']);
                if(is_object($callback) && method_exists($callback, 'make')) {
                    $callback->make();
                }
            }
        }
    }

    /**
     * Get configuration value of extension by config name, extension name and extension type.
     * @param string $name
     * @param string $ext_dir
     * @param string $object
     * @param string $var_type
     * @return bool|int|string
     */
    public function getConfig($name, $ext_dir, $object, $var_type = null)
    {
        $configs = unserialize($this->extconfigs[$object][$ext_dir]['configs']);
        if (in_array($var_type, array('bool', 'boolean', 'bol'))) {
            return $configs[$name] == "0" ? false : true;
        } elseif (in_array($var_type, array('int', 'integer'))) {
            return system::getInstance()->toInt($configs[$name]);
        } elseif(in_array($var_type, array('float', 'double'))) {
            return (float)$configs[$name];
        }
        return $configs[$name];
    }

    public function overloadExtension($type, $dir, $return_data = false) {
        $this->extconfigs[$type][$dir] = null;
        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_extensions WHERE `type` = ? AND `dir` = ?");
        $stmt->bindParam(1, $type, \PDO::PARAM_STR);
        $stmt->bindParam(2, $dir, \PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() == 1) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $this->extconfigs[$type][$dir] = $result;
        }
        $stmt = null;
        if($return_data)
            return $this->extconfigs[$type][$dir];
        return null;
    }

    public function overloadConfigs() {
        $this->extconfigs = null;
        $this->init();
    }

    public function init() { // loadExtensionsData()
        $query = "SELECT * FROM ".property::getInstance()->get('db_prefix')."_extensions";
        $stmt = database::getInstance()->con()->query($query);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach($result as $row) {
            foreach($row as $key=>$value) {
                $this->extconfigs[$row['type']][$row['dir']][$key] = $value;
            }
        }
    }

    /**
     * Get all extensions data as array
     * @return array
     */
    public function getAllParams() {
        return $this->extconfigs;
    }
}