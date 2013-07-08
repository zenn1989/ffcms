<?php


// Отображение статичных блоков из /templates/tpl_name/positions/*
class mod_static_includes_front implements mod_front
{
    public function before()
    {
        global $constant, $page, $template, $system;
        $scandir = scandir($constant->root . "/" . $constant->tpl_dir . "/" . $constant->tpl_name . "/positions/");
        $allowedPositions = $template->allowedPositions();
        foreach($scandir as $files) {
            if(!$system->prefixEquals($files, '.') && $system->suffixEquals($files, '.tpl')) {
                list($position, $index) = $system->altexplode('_', $files);
                $index = strstr($index, '.', true);
                if(in_array($position, $allowedPositions) && $system->isInt($index)) {
                    $page->setContentPosition($position, $template->get(strstr($files, '.', true), 'positions/'), $index);
                }
            }
        }
    }

    public function after()
    {
    }
}

?>