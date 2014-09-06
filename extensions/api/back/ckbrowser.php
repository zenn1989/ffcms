<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\permission;
use engine\system;
use engine\template;
use engine\property;

class api_ckbrowser_back {
    protected static $instance = null;

    const TYPE_ALL = 0;
    const TYPE_IMAGE = 1;
    const TYPE_FLASH = 2;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $tpl = @file_get_contents(root . '/resource/ckeditor/customtpl/filebrowser.tpl');
        $file_type = (int)system::getInstance()->get('type');
        if($tpl == null) {
            echo "<p>Filebrowser tpl was not founded</p>";
            return;
        }
        $file_array = null;
        switch($file_type) {
            case 1:
                if(!permission::getInstance()->have('admin/imagebrowser'))
                    return null;
                $file_array = $this->browseImage();
                break;
            case 2:
                if(!permission::getInstance()->have('admin/flashbrowser'))
                    return null;
                $file_array = $this->browseSwf();
                break;
            default:
                if(!permission::getInstance()->have('admin/filebrowser'))
                    return null;
                $file_array = $this->browseAll();
                break;
        }
        template::getInstance()->justPrint($tpl, array('files' => $file_array, 'file_type' => $file_type, 'ckcallback' => system::getInstance()->get('CKEditorFuncNum')));
    }

    private function browseSwf() {
        $result = array();
        $path = root . '/upload/flash/';

        foreach(scandir($path) as $file) {
            if(system::getInstance()->suffixEquals($file, '.swf')) {
                $result[] = array(
                    'path' => system::getInstance()->get('script_url') . 'upload/flash/' . $file,
                    'name' => system::getInstance()->nohtml($file)
                );
            }
        }

        return $result;
    }

    private function browseAll() {
        $result = array();
        $path = root . '/upload/other/';

        $allow_ext = system::getInstance()->altexplode(';', property::getInstance()->get('upload_allowed_ext'));
        foreach($allow_ext as $key=>$value) { // no dots
            $nodot = str_replace('.', '', $value);
            if(system::getInstance()->length($nodot) > 0)
                $allow_ext[$key] = $nodot;
        }

        foreach(scandir($path) as $file) {
            $f_pharse = system::getInstance()->altexplode('.', $file);
            $file_ext = array_pop($f_pharse);
            if(in_array($file_ext, $allow_ext)) {
                $result[] = array(
                    'path' => system::getInstance()->get('script_url') . 'upload/flash/' . $file,
                    'name' => system::getInstance()->nohtml($file)
                );
            }
        }

        return $result;
    }

    private function browseImage() {
        $result = array();
        $path = root . '/upload/images/';

        foreach(scandir($path) as $file) {
            if(system::getInstance()->suffixEquals($file, '.jpg') || system::getInstance()->suffixEquals($file, '.jpeg')
            || system::getInstance()->suffixEquals($file, '.png') || system::getInstance()->suffixEquals($file, '.gif')) {
                $result[] = array(
                    'path' => system::getInstance()->get('script_url') . 'upload/images/' . $file,
                    'name' => system::getInstance()->nohtml($file)
                );
            }
        }

        return $result;
    }
}