<?php

class mod_static_on_main_back implements backend
{
    public function load()
    {
        global $template,$admin,$language,$system;
        if($admin->getAction() == "turn")
        {
            return $admin->turn();
        }
        $action_page_title = $admin->getExtName()." : ".$language->get('admin_modules_staticonmain_settings');
        $menu_theme = $template->tplget('config_menu', null, true);
        $work_body = null;
        if($system->post('submit'))
        {
            $save_try = $admin->trySaveConfigs();
            if($save_try)
                $work_body .= $template->stringNotify('success', $language->get('admin_extension_config_update_success'), true);
            else
                $work_body .= $template->stringNotify('error', $language->get('admin_extension_config_update_fail'), true);;
        }
        $show_date_news = $admin->tplSettingsSelectYorN('config:show_date', $language->get('admin_modules_staticonmain_settings_showdate_title'), $language->get('admin_modules_staticonmain_settings_showdate_desc'), $admin->getConfig('show_date', 'boolean'));
        $work_body .= $template->assign(array('static_page_option_list', 'config_show_date'), array($this->buildOptionListPages(), $show_date_news), $template->tplget('static_on_main', 'modules/', true));

        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id='.$admin->getID(), $language->get('admin_modules_staticonmain_settings')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

    private function buildOptionListPages()
    {
        global $template,$constant,$database,$admin;
        $saved_value = $admin->getConfig('news_id', 'int');
        $theme_option_active = $template->tplget('form_option_item_active', null, true);
        $theme_option_inactive = $template->tplget('form_option_item_inactive', null, true);
        $stmt = $database->con()->prepare("SELECT `id`, `title` FROM {$constant->db['prefix']}_com_static ORDER BY `id` DESC");
        $stmt->execute();
        $output = null;
        while($result = $stmt->fetch())
        {
            if($result['id'] == $saved_value)
            {
                $output .= $template->assign(array('option_value', 'option_name'), array($result['id'], $result['title']), $theme_option_active);
            }
            else
            {
                $output .= $template->assign(array('option_value', 'option_name'), array($result['id'], $result['title']), $theme_option_inactive);
            }
        }
        $stmt = null;
        return $output;
    }
}



?>