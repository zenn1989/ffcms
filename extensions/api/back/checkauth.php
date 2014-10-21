<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\user;

class api_checkauth_back extends \engine\singleton {

    public function make() {
        echo (int)user::getInstance()->get('id');
        return null;
    }
}