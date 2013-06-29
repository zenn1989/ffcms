<?php

class mod_menu_manager_back implements backend
{
    public function load()
    {
        global $admin;
        if ($admin->getAction() == "turn") {
            return $admin->turn();
        }
        return;
    }
}

?>