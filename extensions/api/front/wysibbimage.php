<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\user;
use engine\property;
use engine\extension;


class api_wysibbimage_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $dir = system::getInstance()->get('dir');
        if(system::getInstance()->isLatinOrNumeric($dir) && system::getInstance()->length($dir) > 0
           && user::getInstance()->get('id') > 0 && $_FILES['img'] != null) {
            $isIframe = ($_POST["iframe"]) ? true : false;
            $idarea = $_POST["idarea"];
            $obj = extension::getInstance()->call(extension::TYPE_HOOK, 'file');
            if(!is_object($obj))
                exit();
            $result = $obj->uploadImage('/'.$dir.'/', $_FILES['img']);
            $fulllink = property::getInstance()->get('script_url')."/upload/{$dir}/" . $result;
            if($isIframe) {
                if($result != null)
                    echo '<html><body>OK<script>window.parent.$("#' . $idarea . '").insertImage("' . $fulllink . '","' . $fulllink . '").closeModal().updateUI();</script></body></html>';
                else
                    echo '<html><body>ERROR<script>window.parent.alert("Image upload error.");</script></body></html>';
            } else {
                header("Content-type: text/javascript");
                if($result != null) {
                    $json_response = array(
                        'status' => '1',
                        'msg' => 'ok',
                        'image_link' => $fulllink,
                        'thumb_link' => $fulllink
                    );
                } else {
                    $json_response = array(
                        'status' => '0',
                        'msg' => 'error'
                    );
                }
                echo stripslashes(json_encode($json_response));
            }
        }
    }
}