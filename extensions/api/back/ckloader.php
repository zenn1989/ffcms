<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\permission;
use engine\language;
use engine\property;
use engine\extension;
use engine\system;

class api_ckloader_back {
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
        if($_FILES['upload'] == null)
            return null;

        $type = (int)system::getInstance()->get('type');
        $result = false;

        $save_folder = false;

        $allow_ext = system::getInstance()->altexplode(';', property::getInstance()->get('upload_allowed_ext'));
        foreach($allow_ext as $key=>$value) { // no dots
            $nodot = str_replace('.', '', $value);
            if(system::getInstance()->length($nodot) > 0)
                $allow_ext[$key] = $nodot;
        }

        switch($type) {
            case 1:
                if(permission::getInstance()->have('admin/imagebrowser')) {
                    $result = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadImage('/images/', $_FILES['upload']);
                    $save_folder = 'images';
                }
                break;
            case 2:
                if(permission::getInstance()->have('admin/flashbrowser')) {
                    $result = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadFile('/flash/', $_FILES['upload'], array('swf'));
                    $save_folder = 'flash';
                }
                break;
            default:
                if(permission::getInstance()->have('admin/filebrowser')) {
                    $result = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadFile('/other/', $_FILES['upload'], $allow_ext);
                    $save_folder = 'other';
                }
                break;
        }

        if(!$result || !$save_folder) {
            echo '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_GET['CKEditorFuncNum'].'", "", "'.language::getInstance()->get('fileupload_api_error').'");</script></body></html>';
        } else {
            echo '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_GET['CKEditorFuncNum'].'", "'.property::getInstance()->get('script_url').'/upload/'.$save_folder.'/'.$result.'");</script></body></html>';
        }
    }
}