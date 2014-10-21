<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\template;
use engine\extension;
use engine\database;
use engine\property;

class modules_tagcloud_front extends \engine\singleton {

    public function make() {
        template::getInstance()->set(template::TYPE_MODULE, 'tag_cloud', $this->buildTagCloud());
    }

    private function buildTagCloud() {
        $tag_count = extension::getInstance()->getConfig('tag_count', 'tagcloud', 'modules', 'int');
        $stmt = database::getInstance()->con()->prepare("SELECT SQL_CALC_FOUND_ROWS tag, COUNT(*) AS count FROM ".property::getInstance()->get('db_prefix')."_mod_tags WHERE object_type = 'news' GROUP BY tag ORDER BY count DESC LIMIT 0,?");
        $stmt->bindParam(1, $tag_count, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return template::getInstance()->twigRender('modules/tagcloud/cloud.tpl', array('local' => $result));
    }
}