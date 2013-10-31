<?php

class mod_tagcloud_back implements backend
{
    public function load()
    {
        global $engine;
        if($engine->admin->getAction() == "turn")
            return $engine->admin->turn();
        $action_page_title = $engine->admin->getExtName() . " : " . $engine->language->get('admin_modules_tagcloud_settings');
        $work_body = null;
        if ($engine->system->post('submit')) {
            $save_try = $engine->admin->trySaveConfigs();
            if ($save_try)
                $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_extension_config_update_success'));
            else
                $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_extension_config_update_fail'));
        }
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id=' . $engine->admin->getID() . '&action=settings', $engine->language->get('admin_modules_tagcloud_settings')), $menu_theme);

        $config_form = $engine->template->get('config_form');
        $config_set = null;

        $config_set .= $engine->admin->tplSettingsInputText('config:tag_count', $engine->admin->getConfig('tag_count', 'int'), $engine->language->get('admin_modules_tagcloud_count_title'), $engine->language->get('admin_modules_tagcloud_count_desc'));
        $config_set .= $engine->admin->tplSettingsInputText('config:template_position_name', $engine->admin->getConfig('template_position_name'), $engine->language->get('admin_modules_tagcloud_label_position_title'), $engine->language->get('admin_modules_tagcloud_label_position_desc'));
        $config_set .= $engine->admin->tplSettingsInputText('config:template_position_index', $engine->admin->getConfig('template_position_index', 'int'), $engine->language->get('admin_modules_tagcloud_label_posindex_title'), $engine->language->get('admin_modules_tagcloud_label_posindex_desc'));
        $work_body .= $engine->template->assign('ext_form', $config_set, $config_form);
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }
}



?>