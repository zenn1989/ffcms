<?php

use engine\permission;
use engine\language;
use engine\property;
use engine\extension;

class api_ckloader_back {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        if(!permission::getInstance()->have('global/owner') || $_FILES['upload'] == null)
            return null;
        $result = extension::getInstance()->call(extension::TYPE_HOOK, 'file')->uploadImage('/images/', $_FILES['upload']);
        if(!$result) {
            echo '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_GET['CKEditorFuncNum'].'", "", "'.language::getInstance()->get('fileupload_api_error').'");</script></body></html>';
        } else {
            echo '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$_GET['CKEditorFuncNum'].'", "'.property::getInstance()->get('script_url').'/upload/images/'.$result.'");</script></body></html>';
        }
    }
}