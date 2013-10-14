<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class com_sitemap_back implements backend
{
    public function load() {
        global $engine;
        if($engine->admin->getAction() == "turn") {
            return $engine->admin->turn();
        }
        return null;
    }
}

?>