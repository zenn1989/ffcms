<?php
class hook_profile_back implements backend
{
    public function load()
    {
        global $admin;
        if($admin->getAction() == "turn")
            return $admin->turn();
        return null;
    }
}

?>