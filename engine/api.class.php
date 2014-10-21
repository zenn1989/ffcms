<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class api extends singleton {
    protected static $links_api = array();

    public function make() {
        $iface = system::getInstance()->get('iface');
        $object = system::getInstance()->get('object');
        $cron = system::getInstance()->get('cron');
        if($cron != null)
            return $this->cronInit();
        $link = $this->call($iface, $object);
        if(method_exists($link, 'make'))
            $link->make();
        return null;
    }

    /**
     * Call to remote function of API classes using interface type and api extension name.
     * @param string $iface
     * @param string $object
     * @return object
     */
    public function call($iface, $object) {
        if($iface == null || $object == null) {
            exit();
        }
        if(!is_object(self::$links_api[$iface][$object])) {
            $file = root . '/extensions/api/' . $iface . '/' . $object . '.php';
            if(file_exists($file)) {
                @require_once($file);
                // ex: api_fileupload_front
                $cname = 'api_'.$object.'_'.$iface;
                if(class_exists($cname)) {
                    $link = new $cname;
                    if(method_exists($link, 'getInstance')) {
                        self::$links_api[$iface][$object] = $link::getInstance();
                    } else {
                        logger::getInstance()->log(logger::LEVEL_WARN, 'Method getInstance() not founded in file '.$file);
                    }
                } else {
                    logger::getInstance()->log(logger::LEVEL_WARN, 'Class '.$cname.' not founded in '.$file);
                }
            } else {
                logger::getInstance()->log(logger::LEVEL_ERR, 'Api file not founded: '.$file);
            }
        }
        return self::$links_api[$iface][$object];
    }

    private function cronInit() {
        foreach(extension::getInstance()->getAllParams() as $ext_type=>$ext_data) {
            foreach($ext_data as $ext_item) {
                if($ext_item['enabled'] == 1) {
                    $ext_file = root . '/extensions/' . $ext_type . '/' . $ext_item['dir'] . '/cron.php';
                    if(file_exists($ext_file)) {
                        @require_once($ext_file);
                        $cname = 'cron_' . $ext_item['dir'];
                        if(class_exists($cname)) {
                            $link = new $cname;
                            if(method_exists($link, 'getInstance') && method_exists($link, 'make'))
                                $link::getInstance()->make();
                            else
                                logger::getInstance()->log(logger::LEVEL_WARN, 'Method getInstance() or make() not founded in cron '.$ext_file);
                        } else {
                            logger::getInstance()->log(logger::LEVEL_WARN, 'Class '.$cname.' not founded in cron '.$ext_file);
                        }
                    }
                }
            }
        }
        return null;
    }
}



