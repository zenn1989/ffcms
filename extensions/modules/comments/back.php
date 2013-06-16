<?php

class mod_comments_back implements backend
{
    public function load()
    {
        global $template,$admin,$language,$system;
        $action_page_title = $admin->getExtName()." : ";
        $work_body = null;
        if($admin->getAction() == "manage" || $admin->getAction() == null)
        {
            $action_page_title .= $language->get('admin_modules_comment_manage_title');

        }
        elseif($admin->getAction() == "settings")
        {
            $action_page_title .= $language->get('admin_modules_comment_settings_title');
            if($system->post('submit'))
            {
                $save_try = $admin->trySaveConfigs();
                if($save_try)
                    $work_body .= $template->stringNotify('success', $language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $template->stringNotify('error', $language->get('admin_extension_config_update_fail'), true);;
            }
            $config_form = $template->tplget('config_form', null, true);
            $config_set = $admin->tplSettingsInputText('config:comments_count', $admin->getConfig('comments_count', 'int'), $language->get('admin_modules_comment_config_count_title'), $language->get('admin_modules_comment_config_count_desc'));
            $config_set .= $admin->tplSettingsInputText('config:time_delay', $admin->getConfig('time_delay', 'int'), $language->get('admin_modules_comment_config_timedelay_title'), $language->get('admin_modules_comment_config_timedelay_desc'));
            $config_set .= $admin->tplSettingsInputText('config:edit_time', $admin->getConfig('edit_time', 'int'), $language->get('admin_modules_comment_config_edittime_title'), $language->get('admin_modules_comment_config_edittime_desc'));
            $work_body .= $template->assign('ext_form', $config_set, $config_form);
        }

        $menu_theme = $template->tplget('config_menu', null, true);
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id='.$admin->getID()."&action=manage", $language->get('admin_modules_comment_manage_title')), $menu_theme);
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=modules&id='.$admin->getID()."&action=settings", $language->get('admin_modules_comment_settings_title')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

}







?>