<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

// Отображение статичных блоков из /templates/tpl_name/positions/*
class mod_static_includes_front implements mod_front
{
    public function before()
    {
        global $engine;
        $scandir = scandir($engine->constant->root . "/" . $engine->constant->tpl_dir . "/" . $engine->constant->tpl_name . "/positions/");
        $allowedPositions = $engine->template->allowedPositions();
        foreach($scandir as $files) {
            if(!$engine->system->prefixEquals($files, '.') && $engine->system->suffixEquals($files, '.tpl')) {
                list($position, $index) = $engine->system->altexplode('_', $files);
                $index = strstr($index, '.', true);
                if(in_array($position, $allowedPositions) && $engine->system->isInt($index)) {
                    $engine->page->setContentPosition($position, $engine->template->get(strstr($files, '.', true), 'positions/'), $index);
                }
            }
        }
    }

    public function after()
    {
    }
}

?>