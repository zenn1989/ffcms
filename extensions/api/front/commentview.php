<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\extension;
use engine\language;

class api_commentview_front extends \engine\singleton {

    public function make() {
        $comment_way = system::getInstance()->post('pathway');
        $comment_position = (int)system::getInstance()->post('comment_position');
        $load_all = system::getInstance()->post('comment_all') === "true" ? true : false; // to bool :D
        $result = extension::getInstance()->call(extension::TYPE_MODULE, 'comments')->buildCommentTemplate($comment_way, $comment_position, $load_all);
        echo $result;
    }
}