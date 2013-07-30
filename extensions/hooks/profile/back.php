<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

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