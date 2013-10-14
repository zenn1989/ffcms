<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class mod_static_on_main_back implements backend
{
    public function load()
    {
        global $engine;
        if ($engine->admin->getAction() == "turn") {
            return $engine->admin->turn();
        }
        $action_page_title = $engine->admin->getExtName() . " : " . $engine->language->get('admin_modules_staticonmain_settings');
        $menu_theme = $engine->template->get('config_menu');
        $work_body = null;
        if ($engine->system->post('submit')) {
            $save_try = $engine->admin->trySaveConfigs();
            if ($save_try)
                $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_extension_config_update_success'), true);
            else
                $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_extension_config_update_fail'), true);;
        }
        $show_date_news = $engine->admin->tplSettingsSelectYorN('config:show_date', $engine->language->get('admin_modules_staticonmain_settings_showdate_title'), $engine->language->get('admin_modules_staticonmain_settings_showdate_desc'), $engine->admin->getConfig('show_date', 'boolean'));
        $work_body .= $engine->template->assign(array('static_page_option_list', 'config_show_date'), array($this->buildOptionListPages(), $show_date_news), $engine->template->get('static_on_main', 'modules/'));

        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id=' . $engine->admin->getID(), $engine->language->get('admin_modules_staticonmain_settings')), $menu_theme);
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function buildOptionListPages()
    {
        global $engine;
        $saved_value = $engine->admin->getConfig('news_id', 'int');
        $theme_option_active = $engine->template->get('form_option_item_active');
        $theme_option_inactive = $engine->template->get('form_option_item_inactive');
        $stmt = $engine->database->con()->prepare("SELECT `id`, `title` FROM {$engine->constant->db['prefix']}_com_static ORDER BY `id` DESC");
        $stmt->execute();
        $output = null;
        while ($result = $stmt->fetch()) {
            $page_serial_name = unserialize($result['title']);
            if ($result['id'] == $saved_value) {
                $output .= $engine->template->assign(array('option_value', 'option_name'), array($result['id'], $page_serial_name[$engine->language->getCustom()]), $theme_option_active);
            } else {
                $output .= $engine->template->assign(array('option_value', 'option_name'), array($result['id'], $page_serial_name[$engine->language->getCustom()]), $theme_option_inactive);
            }
        }
        $stmt = null;
        return $output;
    }
}


?>