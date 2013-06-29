<?php

class mod_news_on_main_back implements backend
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