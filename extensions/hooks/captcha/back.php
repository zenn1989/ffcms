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
        global $engine;
        $config_pharse = null;
        $work_body = null;
        $action_page_title = $engine->admin->getExtName() . " : ";
        $stmt = null;
        if($engine->admin->getAction() == null || $engine->admin->getAction() == "settings") {
            $action_page_title .= $engine->language->get('admin_hook_captcha_settings');

            if ($engine->system->post('submit')) {
                $save_try = $engine->admin->trySaveConfigs();
                if ($save_try)
                    $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_extension_config_update_success'), true);
                else
                    $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_extension_config_update_fail'), true);;
            }

            $config_form = $engine->template->get('config_form');
            $config_set = null;

            $config_set .= $engine->admin->tplSettingsInputText('config:captcha_type', $engine->admin->getConfig('captcha_type'), $engine->language->get('admin_hook_captcha_config_type_title'), $engine->language->get('admin_hook_captcha_config_type_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:captcha_publickey', $engine->admin->getConfig('captcha_publickey'), $engine->language->get('admin_hook_captcha_config_publickey_title'), $engine->language->get('admin_hook_captcha_config_publickey_desc'));
            $config_set .= $engine->admin->tplSettingsInputText('config:captcha_privatekey', $engine->admin->getConfig('captcha_privatekey'), $engine->language->get('admin_hook_captcha_config_privatekey_title'), $engine->language->get('admin_hook_captcha_config_privatekey_desc'));
            $work_body .= $engine->template->assign('ext_form', $config_set, $config_form);
        }
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=hooks&id=' . $engine->admin->getID() . '&action=settings', $engine->language->get('admin_hook_captcha_settings')), $menu_theme);
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;

    }
}



?>