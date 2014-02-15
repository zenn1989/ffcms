<?php
use engine\admin;
class hook_comment_back implements backend
{
    public function load()
    {
        if(admin::getAction() == "turn")
            admin::turn();
        return null;
    }
}



?>