<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class cache extends singleton {
    protected static $instance = null;

    const CACHE_TIME = 120;
    const CACHE_DIR = '/cache/file/';

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::checkDirectoryAvailable();
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected static function checkDirectoryAvailable() {
        if(!file_exists(root . self::CACHE_DIR)) {
            try {
                system::getInstance()->createPrivateDirectory(root . self::CACHE_DIR);
            } catch(\Exception $e) {
                logger::getInstance()->log(logger::LEVEL_ERR, 'File cache directory can`t be created: '.$e->getMessage());
            }
        }
    }

    /**
     * Save data in cache file storage
     * @param string $name
     * @param string $data
     */
    public function store($name, $data) {
        $name = md5($name);
        @file_put_contents(root . self::CACHE_DIR . $name . '.cache', $data);
    }

    /**
     * Alias for function store($name, $data)
     * @param string $name
     * @param string $data
     */
    public function save($name, $data) {
        $this->store($name, $data);
    }

    /**
     * Get file from cache storage if it exist and does not expire for time. Return null if cache not available.
     * @param string $name
     * @param int $custom_time
     * @return null|string
     */
    public function get($name, $custom_time = null) {
        $lifetime = $custom_time < self::CACHE_TIME ? self::CACHE_TIME : $custom_time;
        $name = md5($name);
        if(file_exists(root . self::CACHE_DIR . $name . '.cache')) {
            $mtime = filemtime(root . self::CACHE_DIR . $name . '.cache');
            if(time() - $mtime <= $lifetime)
                return @file_get_contents(root . self::CACHE_DIR . $name . '.cache');
        }
        return null;
    }
}