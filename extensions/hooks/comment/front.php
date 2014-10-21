<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\database;
use engine\property;
use engine\router;

class hooks_comment_front extends \engine\singleton {

    /**
     * Comments count by URI
     * @param string|null $way
     * @return mixed
     */
    public function getCount($way = null)
    {
        if(is_null($way))
            $way = router::getInstance()->getUriString();
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_mod_comments WHERE pathway = ? AND moderate = 0");
        $stmt->bindParam(1, $way, PDO::PARAM_STR);
        $stmt->execute();
        $resultSet = $stmt->fetch();
        return $resultSet[0];
    }
}

?>