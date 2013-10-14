<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class hook_comment_front
{
    public function load()
    {
        return $this;
    }

    /**
     * Количество комментариев к объекту по HASH сниппету
     * @param $hash
     * @return mixed
     */
    public function getCount($hash)
    {
        global $engine;
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_mod_comments WHERE target_hash = ?");
        $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
        $stmt->execute();
        $resultSet = $stmt->fetch();
        return $resultSet[0];
    }
}

?>