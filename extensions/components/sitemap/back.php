<?php
class com_sitemap_back implements backend
{
    public function load() {
        global $admin;
        if($admin->getAction() == "turn") {
            return $admin->turn();
        }
        return null;
    }
}

?>