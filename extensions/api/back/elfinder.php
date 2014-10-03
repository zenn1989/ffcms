<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\permission;
use engine\property;
use engine\system;

class api_elfinder_back extends \engine\singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        if (!permission::getInstance()->have('admin/filemanager')) {
            return;
        }
        include_once root . '/resource/elfinder/php/elFinderConnector.class.php';
        include_once root . '/resource/elfinder/php/elFinder.class.php';
        include_once root . '/resource/elfinder/php/elFinderVolumeDriver.class.php';
        include_once root . '/resource/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
        function access($attr, $path, $data, $volume)
        {
            return strpos(basename($path), '.') === 0 // if file/folder begins with '.' (dot)
                ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
                : null; // else elFinder decide it itself
        }

        if(!file_exists(root . '/upload/'))
            system::getInstance()->createDirectory(root . '/upload/', 0755);

        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                    'path' => root . '/upload/', // path to files (REQUIRED)
                    'URL' => property::getInstance()->get('script_url') . '/upload/', // URL to files (REQUIRED)
                    'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
                )
            )
        );
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }
}