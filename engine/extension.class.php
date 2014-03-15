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

    protected static $instance = null;
    protected static $callobjects = array();
    protected static $extconfigs = array();


    const TYPE_MODULE = 'modules';
    const TYPE_COMPONENT = 'components';
    const TYPE_HOOK = 'hooks';

    /**
     * @return extension
     */
    public static function getInstance($all = false) {
        if(is_null(self::$instance)) {
            self::loadExtensionsData($all);
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function foundRoute($way) {
        if(array_key_exists($way, self::$extconfigs['components']) && $way != null && !system::getInstance()->prefixEquals($way, '.') && self::$extconfigs['components'][$way]['enabled'] == 1)
            return true;
        return false;
    }

    public function call($type, $object) {
        if(!is_object(self::$callobjects[$type][$object])) {
            if(array_key_exists($type, self::$extconfigs) && array_key_exists($object, self::$extconfigs[$type])) {
                $file = root . '/extensions/' . $type . '/' . $object . '/front.php';
                if(file_exists($file)) {
                    @require_once($file);
                    $cname = $type . '_' . $object .'_front';
                    if(class_exists($cname)) {
                        $init = new $cname;
                        if(method_exists($cname, 'getInstance')) {
                            $instance = $init::getInstance();
                            if(is_object($instance)) {
                                self::$callobjects[$type][$object] = $instance;
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
        return self::$callobjects[$type][$object];
    }

    public function loadModules() {
        foreach(self::$extconfigs['modules'] as $mod_data) {
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

    /**
     * @param $name
     * @param $ext_dir
     * @param $object
     * @param string $var_type
     * @return bool|int|string
     */
    public function getConfig($name, $ext_dir, $object, $var_type = null)
    {
        $configs = unserialize(self::$extconfigs[$object][$ext_dir]['configs']);
        if (in_array($var_type, array('bool', 'boolean', 'bol'))) {
            return $configs[$name] == "0" ? false : true;
        } elseif (in_array($var_type, array('int', 'integer'))) {
            return system::getInstance()->toInt($configs[$name]);
        }
        return $configs[$name];
    }

    public function overloadConfigs() {
        self::$extconfigs = null;
        self::loadExtensionsData();
    }

    protected static function loadExtensionsData($all = false) {
        $query = "SELECT * FROM ".property::getInstance()->get('db_prefix')."_extensions";
        if(!$all)
            $query .= " WHERE enabled = 1";
        $stmt = database::getInstance()->con()->query($query);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach($result as $row) {
            foreach($row as $key=>$value) {
                self::$extconfigs[$row['type']][$row['dir']][$key] = $value;
            }
        }
    }

    public function getAllParams() {
        return self::$extconfigs;
    }
}