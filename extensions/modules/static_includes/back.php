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
        global $admin, $template, $language, $constant, $system;
        if ($admin->getAction() == "turn") {
            return $admin->turn();
        }
        $action_page_title = $admin->getExtName() . " : " . $language->get('admin_modules_static_includes_menuinfo');
        $toDrowData = array();
        $scandir = scandir($constant->root . "/" . $constant->tpl_dir . "/" . $constant->tpl_name . "/positions/");
        $allowedPositions = $template->allowedPositions();
        foreach($scandir as $files) {
            if(!$system->prefixEquals($files, '.') && $system->suffixEquals($files, '.tpl')) {
                list($position, $index) = $system->altexplode('_', $files);
                $index = strstr($index, '.', true);
                if(in_array($position, $allowedPositions) && $system->isInt($index)) {
                    $toDrowData[] = array($position, $index, $files);
                }
            }
        }
        $rawTable = $admin->tplRawTable(array('Позиция', 'Индекс', 'Файл'), $toDrowData);
        $work_body = $template->assign(array('table_data', 'template_directory_position'), array($rawTable, $constant->tpl_dir."/".$constant->tpl_name."/positions/"), $template->get('static_includes', 'modules/'));
        $menu_theme = $template->get('config_menu');
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id=' . $admin->getID(), $language->get('admin_modules_static_includes_menuinfo')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->get('config_head'));
        return $body_form;
    }
}

?>