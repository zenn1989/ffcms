<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Хук отвечающий за капчу
 * В будующем доработать конфигурации для разных капч (recaptcha, kcaptcha, etc)
 */

class hook_captcha_back implements backend
{
    public function load()
    {
        global $template, $admin, $language, $system;
        $config_pharse = null;
        $work_body = null;
        $action_page_title = $admin->getExtName() . " : ";
        $stmt = null;
        if($admin->getAction() == null || $admin->getAction() == "settings") {
            $action_page_title .= $language->get('admin_hook_captcha_settings');

            if ($system->post('submit')) {
                $save_try = $admin->trySaveConfigs();
                if ($save_try)
                    $work_body .= $template->stringNotify('success', $language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $template->stringNotify('error', $language->get('admin_extension_config_update_fail'), true);;
            }

            $config_form = $template->get('config_form');
            $config_set = null;

            $config_set .= $admin->tplSettingsInputText('config:captcha_type', $admin->getConfig('captcha_type'), $language->get('admin_hook_captcha_config_type_title'), $language->get('admin_hook_captcha_config_type_desc'));
            $config_set .= $admin->tplSettingsInputText('config:captcha_publickey', $admin->getConfig('captcha_publickey'), $language->get('admin_hook_captcha_config_publickey_title'), $language->get('admin_hook_captcha_config_publickey_desc'));
            $config_set .= $admin->tplSettingsInputText('config:captcha_privatekey', $admin->getConfig('captcha_privatekey'), $language->get('admin_hook_captcha_config_privatekey_title'), $language->get('admin_hook_captcha_config_privatekey_desc'));
            $work_body .= $template->assign('ext_form', $config_set, $config_form);
        }
        $menu_theme = $template->get('config_menu');
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=hooks&id=' . $admin->getID() . '&action=settings', $language->get('admin_hook_captcha_settings')), $menu_theme);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->get('config_head'));
        return $body_form;

    }
}



?>