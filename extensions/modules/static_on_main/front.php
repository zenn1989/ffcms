<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\extension;
use engine\template;

class modules_static_on_main_front extends \engine\singleton {

    public function make() {
        $page_id = extension::getInstance()->getConfig('news_id', 'static_on_main', 'modules', 'int');
        $show_date = extension::getInstance()->getConfig('show_date', 'static_on_main', 'modules', 'boolean');
        // call to component static pages and display it
        $page_content = extension::getInstance()->call(extension::TYPE_COMPONENT, 'static');
        if(!is_object($page_content))
            return;
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $page_content->display('', $page_id, $show_date, true));
    }
}