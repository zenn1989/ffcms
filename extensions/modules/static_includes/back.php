<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class mod_static_includes_back implements backend
{
    public function load()
    {
        global $engine;
        if ($engine->admin->getAction() == "turn") {
            return $engine->admin->turn();
        }
        $action_page_title = $engine->admin->getExtName() . " : " . $engine->language->get('admin_modules_static_includes_menuinfo');
        $toDrowData = array();
        $scandir = scandir($engine->constant->root . "/" . $engine->constant->tpl_dir . "/" . $engine->constant->tpl_name . "/positions/");
        $allowedPositions = $engine->template->allowedPositions();
        foreach($scandir as $files) {
            if(!$engine->system->prefixEquals($files, '.') && $engine->system->suffixEquals($files, '.tpl')) {
                list($position, $index) = $engine->system->altexplode('_', $files);
                $index = strstr($index, '.', true);
                if(in_array($position, $allowedPositions) && $engine->system->isInt($index)) {
                    $toDrowData[] = array($position, $index, $files);
                }
            }
        }
        $rawTable = $engine->admin->tplRawTable(array('Позиция', 'Индекс', 'Файл'), $toDrowData);
        $work_body = $engine->template->assign(array('table_data', 'template_directory_position'), array($rawTable, $engine->constant->tpl_dir."/".$engine->constant->tpl_name."/positions/"), $engine->template->get('static_includes', 'modules/'));
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id=' . $engine->admin->getID(), $engine->language->get('admin_modules_static_includes_menuinfo')), $menu_theme);
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }
}

?>