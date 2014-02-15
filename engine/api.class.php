<?php
namespace engine;

class api {
    protected static $instance = null;
    protected static $links_api = array();

    /**
     * @return api
     */
    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $iface = system::getInstance()->get('iface');
        $object = system::getInstance()->get('object');
        $link = $this->call($iface, $object);
        if(method_exists($link, 'make'))
            $link->make();
    }

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
}



