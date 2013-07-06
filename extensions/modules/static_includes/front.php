<?php


// Отображение статичных блоков.
class mod_static_includes_front implements mod_front
{
    public function before()
    {
        global $database, $page, $constant, $template;
        $stmt = $database->con()->query("SELECT * FROM {$constant->db['prefix']}_mod_static_includes");
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $page->setContentPosition($result['tag'], $template->setDefaults($result['template']), $result['index']);
        }
    }

    public function after()
    {
    }
}

?>