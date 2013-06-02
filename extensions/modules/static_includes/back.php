<?php

class mod_static_includes_back implements backend
{
    public function load()
    {
        global $admin;
        if($admin->getAction() == "turn")
        {
            return $admin->turn();
        }
        return;
    }
}

?>