<?php
/**
 * Получение хуков
 */
class hook
{
    private $hook_list = array();
    private $hook_link = array();

    function hook()
    {
        global $database, $constant;
        if($database->isDown())
            return;
        $query = "SELECT * FROM {$constant->db['prefix']}_hooks WHERE enabled = 1";
        $stmt = $database->con()->query($query);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $this->hook_list[$row['type']] = $row['dir'];
        }
    }

    public function get($type)
    {
        global $constant;
        if ($this->hook_list[$type] == null) {
            return null;
        }
        if($this->hook_link[$type] != null)
            return $this->hook_link[$type];
        $file = $constant->root . '/extensions/hooks/' . $this->hook_list[$type] . '/front.php';
        if (!file_exists($file)) {
            return null;
        }
        require_once($file);
        $class = "hook_{$this->hook_list[$type]}_front";
        $init = new $class;
        $object = $init->load();
        $this->hook_link[$type] = $object;
        return $object;
    }

    public function after()
    {
        global $constant;
        foreach ($this->hook_list as $key => $index) {
            $file = $constant->root . '/extensions/hooks/' . $index . '/front.php';
            if (file_exists($file)) {
                require_once($file);
                $class = "hook_{$index}_front";
                if (class_exists($class)) {
                    $int = new $class;
                    if (method_exists($int, 'after') && method_exists($int, 'load')) {
                        $int->load()->after();
                    }
                }
            }
        }
    }

    public function before()
    {
        global $constant;
        foreach ($this->hook_list as $key => $index) {
            $file = $constant->root . '/extensions/hooks/' . $index . '/front.php';
            if (file_exists($file)) {
                require_once($file);
                $class = "hook_{$index}_front";
                if (class_exists($class)) {
                    $int = new $class;
                    if (method_exists($int, 'before') && method_exists($int, 'load')) {
                        $int->load()->before();
                    }
                }
            }
        }
    }
}

?>