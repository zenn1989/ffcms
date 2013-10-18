<?php

// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 *
 * @author zenn
 * Класс отвечающий за административную панель управления
 */
class admin
{
    private $object = null;
    private $action = null;
    private $add = null;
    private $id = 0;
    private $page = 0;

    private $object_name = null;

    function admin()
    {
        global $system;
        $this->object = (string)$system->get('object');
        $this->action = (string)$system->get('action');
        $this->add = (string)$system->get('add');
        $this->id = (int)$system->get('id');
        $this->page = (int)$system->get('page');
    }


    /**
     * Загрузка админ панели. Возвращает скомпилированный вариант вместе с шаблоном.
     */
    public function doload()
    {
        global $engine;
        if ($engine->user->get('id') == NULL) {
            $engine->system->redirect('/login');
        } elseif ($engine->user->get('access_to_admin') != 1) {
            $engine->system->redirect();
        } else {
            switch ($this->object) {
                case "components":
                    $engine->page->setContentPosition('body', $this->loadComponents());
                    break;
                case "modules":
                    $engine->page->setContentPosition('body', $this->loadModules());
                    break;
                case "hooks":
                    $engine->page->setContentPosition('body', $this->loadHooks());
                    break;
                case "settings":
                    $engine->page->setContentPosition('body', $this->loadSystemConfigs());
                    break;
                case "filemanager":
                    $engine->page->setContentPosition('body', $this->loadFileManager());
                    break;
                case "antivirus":
                    $engine->page->setContentPosition('body', $this->loadAntiVirus());
                    break;
                case "dump":
                    $engine->page->setContentPosition('body', $this->loadDumper());
                    break;
                default:
                    $engine->page->setContentPosition('body', $this->loadMainPage());
                    break;
            }
            $engine->template->set('username', $engine->user->get('nick'));
        }
        $header = $this->foreachMenuPositions();
        $engine->page->setContentPosition('header', $header);
        $engine->template->init();
        return $engine->template->compile();
    }

    private function loadDumper()
    {
        global $engine;
        $action_page_title = $engine->language->get('admin_nav_li_backup') . " : ";
        $menu_link = $this->loadSystemMenuPositions();
        $work_body = null;
        if ($this->getAction() == "export" || $this->getAction() == null) {
            if ($engine->system->post('submit')){
                $engine->backup->makeDump();
            }
            $action_page_title .= $engine->language->get('admin_dump_export');
            $reader = scandir($engine->constant->root . "/backup/");
            $date_array = array();
            foreach($reader as $files) {
                if(!$engine->system->prefixEquals($files, '.')) {
                    $file_date_array = $engine->system->altexplode('_', $files);
                    $file_date = array_shift($file_date_array);
                    $date_array = $engine->system->arrayAdd($engine->system->toUnixTime($file_date), $date_array);
                }
            }
            arsort($date_array);
            $last_backup_date = null;
            if($date_array[0] == null)
            {
                $last_backup_date = $engine->language->get('admin_dump_notexist');
            }
            else
            {
                $last_backup_date = $engine->system->toDate($date_array[0], 'd');
            }
            $work_body = $engine->template->assign('last_backup_date', $last_backup_date, $engine->template->get('backup_export'));
        } elseif ($this->getAction() == "import") {
            $action_page_title .= $engine->language->get('admin_dump_import');
            $scan = scandir($engine->constant->root . "/backup");
            $date_array = array();
            foreach($scan as $file) {
                if(!$engine->system->prefixEquals($file, ".")) {
                    $file_explode = $engine->system->altexplode('_', $file);
                    $file_date = array_shift($file_explode);
                    $date_array = $engine->system->arrayAdd($engine->system->toUnixTime($file_date), $date_array);
                }
            }
            arsort($date_array);
            $raw_prepare_array = array();
            foreach($date_array as $file_date) {
                $read_date = $engine->system->toDate($file_date, 'd');
                $raw_prepare_array[] = array($read_date, '/backup/'.$read_date.'_www.zip', '/backup/'.$read_date.'_sql.sql.gz');
            }
            $raw_table = $this->tplRawTable(array($engine->language->get('admin_dump_th_date'), $engine->language->get('admin_dump_th_www'), $engine->language->get('admin_dump_th_sql')), $raw_prepare_array);
            $work_body = $engine->template->assign('raw_table_files', $raw_table, $engine->template->get('backup_import'));
        }
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function loadAntiVirus()
    {
        global $engine;
        $action_page_title = $engine->language->get('admin_nav_li_avir');
        $menu_link = $this->loadSystemMenuPositions();
        if($this->getAction() == "exclude") {
            $work_body = $engine->template->get('antivirus_exclude');
            $work_table = null;
            $table_theme = $engine->template->get('antivirus_exclude_tbody');
            if($this->add != null && file_exists($engine->constant->root."/cache/.antivir_exclude")) {
                $exc_array = $engine->system->altexplode('<=>', file_get_contents($engine->constant->root."/cache/.antivir_exclude"));
                foreach($exc_array as $exc_key => $exc_value) {
                    if($exc_value == $this->add)
                        unset($exc_array[$exc_key]);
                }
                file_put_contents($engine->constant->root."/cache/.antivir_exclude", $engine->system->altimplode('<=>', $exc_array));
            }
            if($engine->system->post('antivir_exclude') && $engine->framework->fromPost('antivir_dir')->length() > 2) {
                $exclude_dir = $engine->system->post('antivir_dir');
                if(!$engine->system->suffixEquals($exclude_dir, '/'))
                    $exclude_dir .= "/";
                // существует ли такая директория?
                if(file_exists($engine->constant->root.$exclude_dir)) {
                    file_put_contents($engine->constant->root."/cache/.antivir_exclude", $exclude_dir."<=>", FILE_APPEND);
                    $work_body .= $engine->template->stringNotify('success', $engine->language->get('admin_antivirus_exclude_notify_successadd'));
                } else {
                    // если нет - уведомляем о добавлении несуществующей директории
                    $work_body .= $engine->template->stringNotify('error', $engine->language->get('admin_antivirus_exclude_notify_notfound'));
                }
            }
            if(file_exists($engine->constant->root."/cache/.antivir_exclude")) {
                $exclude_cache = file_get_contents($engine->constant->root."/cache/.antivir_exclude");
                $exclude_array = $engine->system->altexplode('<=>', $exclude_cache);
                foreach($exclude_array as $exclude) {
                    $work_table .= $engine->template->assign(array('root_directory', 'excluded_directory'), array($engine->constant->root, $exclude), $table_theme);
                }
                $work_body = $engine->template->assign('antivirus_exclude_body', $work_table, $work_body);
            }
        } else {
            $work_body = $engine->template->get('antivirus_list');
            $clear_files = null;
            $unknown_files = null;
            $wrong_files = null;
            $hack_files = null;
            if ($engine->system->post('submit') || !file_exists($engine->constant->root . "/cache/.avir_scan")) {
                $engine->antivirus->doFullScan();
                foreach ($engine->antivirus->getClearList() as $file => $md5) {
                    $clear_files .= $engine->template->stringNotify('success', $file . " => " . $md5, true);
                }
                foreach ($engine->antivirus->getUnknownList() as $file => $md5) {
                    $unknown_files .= $engine->template->stringNotify('warning', $file . " => " . $md5, true);
                }
                foreach ($engine->antivirus->getWrongList() as $file => $md5) {
                    $wrong_files .= $engine->template->stringNotify('error', $file . " => " . $md5, true);
                }
                foreach ($engine->antivirus->getHackList() as $file => $md5) {
                    $hack_files .= $engine->template->stringNotify('error', $file . " => " . $md5, true);
                }
                if ($hack_files == null) {
                    $hack_files = $engine->template->stringNotify('success', $engine->language->get('admin_antivirus_cleaall'));
                }
                if ($wrong_files == null) {
                    $wrong_files = $engine->template->stringNotify('success', $engine->language->get('admin_antivirus_cleaall'));
                }
                if ($unknown_files == null) {
                    $unknown_files = $engine->template->stringNotify('success', $engine->language->get('admin_antivirus_cleaall'));
                }
                $work_body = $engine->template->assign(array('avir_clear', 'avir_unknown', 'avir_wrong', 'avir_injected'), array($clear_files, $unknown_files, $wrong_files, $hack_files), $work_body);
                file_put_contents($engine->constant->root . "/cache/.avir_scan", $work_body);
            } else {
                $work_body = file_get_contents($engine->constant->root . "/cache/.avir_scan");
            }
        }
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function loadFileManager()
    {
        global $engine;
        $action_page_title = $engine->language->get('admin_nav_li_filemanager');
        $menu_link = $this->loadSystemMenuPositions();
        $work_body = $engine->template->get('file_manager');
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function loadSystemMenuPositions()
    {
        global $engine;
        $menu_theme = $engine->template->get('config_menu');
        $menu_link = null;
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=settings', $engine->language->get('admin_nav_li_settings')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=filemanager', $engine->language->get('admin_nav_li_filemanager')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=antivirus', $engine->language->get('admin_nav_li_avir')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=dump&action=export', $engine->language->get('admin_dump_export')), $menu_theme);
        $menu_link .= $engine->template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=dump&action=import', $engine->language->get('admin_dump_import')), $menu_theme);
        return $menu_link;
    }

    private function loadSystemConfigs()
    {
        global $engine, $config;
        if ($engine->system->post('submit')) {
            $save_data = "<?php\n";
            foreach ($engine->system->post_data as $var_name => $var_value) {
                if ($engine->system->prefixEquals($var_name, 'cfgmain:')) {
                    $var_clear_name = str_replace("cfgmain:", "", $var_name);
                    // boolean type
                    if ($var_value == "1" || $var_value == "0") {
                        $boolean_var = $var_value == "1" ? true : false;
                        $save_data .= '$config[\'' . $var_clear_name . '\'] = ' . $var_value . ';' . "\n";
                    } else {
                        $save_data .= '$config[\'' . $var_clear_name . '\'] = "' . $var_value . '";' . "\n";
                    }
                }
            }
            $save_data .= "?>";
            if (is_readable($engine->constant->root . $engine->constant->ds . "config.php") && is_writable($engine->constant->root . $engine->constant->ds . "config.php")) {
                file_put_contents($engine->constant->root . $engine->constant->ds . "config.php", $save_data);
            }
            $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=settings&action=saved");
        }
        $action_page_title = $engine->language->get('admin_settings_title');
        $menu_link = $this->loadSystemMenuPositions();

        $work_body = $engine->template->get('settings');
        $theme_option_active = $engine->template->get('form_option_item_active');
        $theme_option_inactive = $engine->template->get('form_option_item_inactive');
        // строчные конфигурации, редактируемые вручную через input.text
        foreach ($config as $cfg_name => $cfg_value) {
            if ($cfg_name == 'tpl_name') {
                $theme_scan_option = null;
                $scan = scandir($engine->constant->root . $engine->constant->ds . $engine->constant->tpl_dir);
                foreach ($scan as $found_tpl) {
                    if ($found_tpl != '.' && $found_tpl != '..' && !$engine->system->contains('.', $found_tpl) && !$engine->system->contains('admin', $found_tpl) && !$engine->system->contains('install', $found_tpl)) {
                        if ($found_tpl == $engine->constant->tpl_name) {
                            $theme_scan_option .= $engine->template->assign(array('option_value', 'option_name'), $found_tpl, $theme_option_active);
                        } else {
                            $theme_scan_option .= $engine->template->assign(array('option_value', 'option_name'), $found_tpl, $theme_option_inactive);
                        }
                    }
                }
                $work_body = $engine->template->assign('settings_tpl_name_list', $theme_scan_option, $work_body);
            } elseif($cfg_name == "lang") {
                $lang_scan_option = null;
                foreach($engine->language->getAvailable() as $allowed_lang) {
                    if($allowed_lang == $cfg_value) {
                        $lang_scan_option .= $engine->template->assign(array('option_value', 'option_name'), $allowed_lang, $theme_option_active);
                    } else {
                        $lang_scan_option .= $engine->template->assign(array('option_value', 'option_name'), $allowed_lang, $theme_option_inactive);
                    }
                }
                $work_body = $engine->template->assign('settings_lang_list', $lang_scan_option, $work_body);
            } elseif($cfg_name == "time_zone") {
                $timezone_option = null;
                $timezone_array = array('Pacific/Kwajalein', 'Pacific/Samoa', 'US/Hawaii', 'US/Alaska', 'US/Pacific', 'US/Arizona', 'America/Mexico_City', 'S/East-Indiana', 'America/Santiago', 'America/Buenos_Aires', 'Brazil/DeNoronha', 'Atlantic/Cape_Verde', 'Europe/London', 'Europe/Berlin', 'Europe/Kiev', 'Europe/Moscow', 'Europe/Samara', 'Asia/Yekaterinburg', 'Asia/Novosibirsk', 'Asia/Krasnoyarsk', 'Asia/Irkutsk', 'Asia/Yakutsk', 'Asia/Vladivostok', 'Asia/Magadan', 'Asia/Kamchatka', 'Pacific/Tongatapu', 'Pacific/Kiritimati');
                foreach($timezone_array as $timezone_select) {
                    if($cfg_value == $timezone_select) {
                        $timezone_option .= $engine->template->assign(array('option_value', 'option_name'), $timezone_select, $theme_option_active);
                    } else {
                        $timezone_option .= $engine->template->assign(array('option_value', 'option_name'), $timezone_select, $theme_option_inactive);
                    }
                }
                $work_body = $engine->template->assign('settings_timezone_list', $timezone_option, $work_body);
            } elseif ($cfg_name == 'debug') {
                $theme_debug_option = null;
                if ($cfg_value == true) {
                    $theme_debug_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_active);
                    $theme_debug_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_debug_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_debug_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $engine->template->assign('settings_debug_list', $theme_debug_option, $work_body);
            } elseif ($cfg_name == 'multi_title') {
                $theme_multititle_option = null;
                if ($cfg_value == true) {
                    $theme_multititle_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_active);
                    $theme_multititle_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_multititle_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_multititle_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $engine->template->assign('settings_multi_title_list', $theme_multititle_option, $work_body);
            } elseif ($cfg_name == "mail_smtp_use") {
                $theme_smtpuse_option = null;
                if ($cfg_value == true) {
                    $theme_smtpuse_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_active);
                    $theme_smtpuse_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_smtpuse_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_smtpuse_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $engine->template->assign('settings_smtp_use_list', $theme_smtpuse_option, $work_body);
            } elseif ($cfg_name == "mail_smtp_auth") {
                $theme_smtpauth_option = null;
                if ($cfg_value == true) {
                    $theme_smtpauth_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_active);
                    $theme_smtpauth_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_smtpauth_option .= $engine->template->assign(array('option_value', 'option_name'), array(0, $engine->language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_smtpauth_option .= $engine->template->assign(array('option_value', 'option_name'), array(1, $engine->language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $engine->template->assign('settings_smtp_auth_list', $theme_smtpauth_option, $work_body);
            }
            if (!is_bool($cfg_value))
                $work_body = $engine->template->assign('config.' . $cfg_name, $cfg_value, $work_body);
        }
        if ($this->getAction() == "saved") {
            $work_body = $engine->template->assign('notify', $engine->template->stringNotify('success', $engine->language->get('admin_settings_saved')), $work_body);
        }
        $body_form = $engine->template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $engine->template->get('config_head'));
        return $body_form;
    }

    private function loadHooks()
    {
        global $engine;
        if($this->action == "install") {
            $ext_name = $this->add;
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_hooks WHERE dir = ?");
            $stmt->bindParam(1, $ext_name, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetch();
            $countFind = $res[0];
            $stmt = null;
            $theme_install = $engine->template->get('extension_install');
            $notify = null;
            if($countFind == 0) {
                $front_file = $engine->constant->root . "/extensions/hooks/" . $ext_name . "/front.php";
                $back_file = $engine->constant->root . "/extensions/hooks/" . $ext_name . "/back.php";
                if(file_exists($front_file) && file_exists($back_file)) {
                    require_once($back_file);
                    $class_name = "hook_" . $ext_name ."_back";
                    if(class_exists($class_name)) {
                        $loader_class = new $class_name;
                        if(method_exists($loader_class, 'install')) {
                            $loader_class->install();
                            $notify .= $engine->template->stringNotify('success', $engine->language->get('extension_install_success'));
                        } else {
                            $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_methodno'));
                        }
                    } else {
                        $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_classno'));
                    }
                } else {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_filenotfound'));
                }
            } else {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_always'));
            }
            return $engine->template->assign('notify', $notify, $theme_install);
        } elseif ($this->hook_exists()) {
            // хук существует, обращаемся к настройке
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_hooks WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $component_back = $engine->constant->root . '/extensions/hooks/' . $result['dir'] . '/back.php';
            $backend_config = null;
            if (file_exists($component_back)) {
                $this->object_name = $engine->language->get('admin_hook_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_hook_'.$result['dir'].'.name');
                require_once($component_back);
                $class = "hook_{$result['dir']}_back";
                $init = new $class;
                $backend_config = $init->load();
            }
            if ($backend_config == null || strlen($backend_config) < 1) {
                $backend_config = $this->showNull();
            }
            return $backend_config;
        } else {
            $theme = $engine->template->assign(
                array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
                array($engine->language->get('admin_hooks_title'), $engine->language->get('admin_hooks_tab_all'), $engine->language->get('admin_hooks_tab_enabled'), $engine->language->get('admin_hooks_tab_dissabled'), $engine->language->get('admin_hooks_tab_toinstall')),
                $engine->template->get('extension_list'));
            $thead = $engine->template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
                array($engine->language->get('admin_hooks_table_th_1'), $engine->language->get('admin_hooks_table_th_2'), $engine->language->get('admin_hooks_table_th_3'), $engine->language->get('admin_hooks_table_th_4'),),
                $engine->template->get('extension_thead'));
            $tbody = $engine->template->get('extension_tbody');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_hooks");
            $stmt->execute();
            $prepare_theme = array();
            $installed_ext_array = array();
            while ($result = $stmt->fetch()) {
                $config_link = "?object=hooks&id=" . $result['id'];
                $hook_name = $engine->language->get('admin_hook_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_hook_'.$result['dir'].'.name');
                $hook_desc = $engine->language->get('admin_hook_'.$result['dir'].'.desc') == null ? $result['dir'] : $engine->language->get('admin_hook_'.$result['dir'].'.desc');
                // вносим в список отключенных
                if ($result['enabled'] == 0) {
                    $iconset = $engine->template->assign(array('ext_config_link', 'ext_turn_link'), array($config_link, '?object=hooks&id=' . $result['id'] . '&action=turn'), $engine->template->get('manage_noactive'));
                    $prepare_theme['dissabled'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $hook_name, $hook_desc, $iconset, $config_link),
                        $tbody);
                } // иначе вносим в список включенных
                else {
                    $iconset = $engine->template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=hooks&id=' . $result['id'], '?object=hooks&id=' . $result['id'] . '&action=turn'), $engine->template->get('manage_active'));
                    $prepare_theme['enabled'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $hook_name, $hook_desc, $iconset, $config_link),
                        $tbody);
                }
                $iconset = $engine->template->assign('ext_config_link', '?object=hooks&id=' . $result['id'], $engine->template->get('manage_all'));
                $prepare_theme['all'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                    array($result['id'], $hook_name, $hook_desc, $iconset, $config_link),
                    $tbody);
                $installed_ext_array[] = $result['dir'];
            }
            $all_availed_ext = scandir($engine->constant->root . '/extensions/hooks/');
            foreach($all_availed_ext as $current_ext) {
                if(!$engine->system->prefixEquals($current_ext, '.') && !in_array($current_ext, $installed_ext_array)) {
                    $iconset = $engine->template->assign('ext_turn_link', '?object=hooks&action=install&add='.$current_ext, $engine->template->get('manage_install'));
                    $prepare_theme['toinstall'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array(0, $current_ext, "Uninstalled extension in folder /extension/".$this->object."/".$current_ext, $iconset),
                        $tbody);
                }
            }
            $alllist = $engine->template->assign('extension_tbody', $prepare_theme['all'], $thead);
            $toinstalllist = $engine->template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
            $activelist = $engine->template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
            $noactivelist = $engine->template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
            $theme = $engine->template->assign(
                array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
                array($alllist, $toinstalllist, $activelist, $noactivelist),
                $theme
            );
            return $theme;

        }
    }

    private function hook_exists()
    {
        global $engine;
        if ($this->id < 1) {
            return false;
        }
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_hooks WHERE id = ?");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result[0] == 1) {
            return true;
        }
        return false;
    }

    private function loadModules()
    {
        global $engine;
        if($this->action == "install") {
            $ext_name = $this->add;
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_modules WHERE dir = ?");
            $stmt->bindParam(1, $ext_name, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetch();
            $countFind = $res[0];
            $stmt = null;
            $theme_install = $engine->template->get('extension_install');
            $notify = null;
            if($countFind == 0) {
                $front_file = $engine->constant->root . "/extensions/modules/" . $ext_name . "/front.php";
                $back_file = $engine->constant->root . "/extensions/modules/" . $ext_name . "/back.php";
                if(file_exists($front_file) && file_exists($back_file)) {
                    require_once($back_file);
                    $class_name = "mod_" . $ext_name ."_back";
                    if(class_exists($class_name)) {
                        $loader_class = new $class_name;
                        if(method_exists($loader_class, 'install')) {
                            $loader_class->install();
                            $notify .= $engine->template->stringNotify('success', $engine->language->get('extension_install_success'));
                        } else {
                            $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_methodno'));
                        }
                    } else {
                        $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_classno'));
                    }
                } else {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_filenotfound'));
                }
            } else {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_always'));
            }
            return $engine->template->assign('notify', $notify, $theme_install);
        } elseif ($this->module_exits()) {
            // модуль существует, выгружаем backend
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_modules WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $component_back = $engine->constant->root . '/extensions/modules/' . $result['dir'] . '/back.php';
            $mod_name = $engine->language->get('admin_modules_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_modules_'.$result['dir'].'.name');
            $backend_config = null;
            if (file_exists($component_back)) {
                $this->object_name = $mod_name;
                require_once($component_back);
                $class = "mod_{$result['dir']}_back";
                $init = new $class;
                $backend_config = $init->load();
            }
            if ($backend_config == null || strlen($backend_config) < 1) {
                $backend_config = $this->showNull();
            }
            return $backend_config;
        } else {
            $theme = $engine->template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
                array($engine->language->get('admin_modules_title'), $engine->language->get('admin_modules_tab_all'), $engine->language->get('admin_modules_tab_enabled'), $engine->language->get('admin_modules_tab_dissabled'), $engine->language->get('admin_modules_tab_toinstall')),
                $engine->template->get('extension_list'));
            $thead = $engine->template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
                array($engine->language->get('admin_modules_table_th_1'), $engine->language->get('admin_modules_table_th_2'), $engine->language->get('admin_modules_table_th_3'), $engine->language->get('admin_modules_table_th_4'),),
                $engine->template->get('extension_thead'));
            $tbody = $engine->template->get('extension_tbody');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_modules");
            $stmt->execute();
            $prepare_theme = array();
            $installed_ext_array = array();
            while ($result = $stmt->fetch()) {
                $config_link = "?object=modules&id=" . $result['id'];
                $mod_name = $engine->language->get('admin_modules_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_modules_'.$result['dir'].'.name');
                $mod_desc = $engine->language->get('admin_modules_'.$result['dir'].'.desc') == null ? $result['dir'] : $engine->language->get('admin_modules_'.$result['dir'].'.desc');
                // вносим в список отключенных
                if ($result['enabled'] == 0) {
                    $iconset = $engine->template->assign(array('ext_config_link', 'ext_turn_link'), array($config_link, '?object=modules&id=' . $result['id'] . '&action=turn'), $engine->template->get('manage_noactive'));
                    $prepare_theme['dissabled'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $mod_name, $mod_desc, $iconset, $config_link),
                        $tbody);
                } // иначе вносим в список включенных
                else {
                    $iconset = $engine->template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=modules&id=' . $result['id'], '?object=modules&id=' . $result['id'] . '&action=turn'), $engine->template->get('manage_active'));
                    $prepare_theme['enabled'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $mod_name, $mod_desc, $iconset, $config_link),
                        $tbody);
                }
                $iconset = $engine->template->assign('ext_config_link', '?object=modules&id=' . $result['id'], $engine->template->get('manage_all'));
                $prepare_theme['all'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                    array($result['id'], $mod_name, $mod_desc, $iconset, $config_link),
                    $tbody);
                $installed_ext_array[] = $result['dir'];
            }
            $all_availed_ext = scandir($engine->constant->root . '/extensions/modules/');
            foreach($all_availed_ext as $current_ext) {
                if(!$engine->system->prefixEquals($current_ext, '.') && !in_array($current_ext, $installed_ext_array)) {
                    $iconset = $engine->template->assign('ext_turn_link', '?object=modules&action=install&add='.$current_ext, $engine->template->get('manage_install'));
                    $prepare_theme['toinstall'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array(0, $current_ext, "Uninstalled extension in folder /extension/".$this->object."/".$current_ext, $iconset),
                        $tbody);
                }
            }
            $alllist = $engine->template->assign('extension_tbody', $prepare_theme['all'], $thead);
            $toinstalllist = $engine->template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
            $activelist = $engine->template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
            $noactivelist = $engine->template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
            $theme = $engine->template->assign(
                array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
                array($alllist, $toinstalllist, $activelist, $noactivelist),
                $theme
            );
            return $theme;
        }
    }

    private function module_exits()
    {
        global $engine;
        if ($this->id < 1) {
            return false;
        }
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_modules WHERE id = ?");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result[0] == 1) {
            return true;
        }
        return false;
    }

    private function loadComponents()
    {
        global $engine;
        if($this->action == "install") {
            $ext_name = $this->add;
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_components WHERE dir = ?");
            $stmt->bindParam(1, $ext_name, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetch();
            $countFind = $res[0];
            $stmt = null;
            $theme_install = $engine->template->get('extension_install');
            $notify = null;
            if($countFind == 0) {
                $front_file = $engine->constant->root . "/extensions/components/" . $ext_name . "/front.php";
                $back_file = $engine->constant->root . "/extensions/components/" . $ext_name . "/back.php";
                if(file_exists($front_file) && file_exists($back_file)) {
                    require_once($back_file);
                    $class_name = "com_" . $ext_name ."_back";
                    if(class_exists($class_name)) {
                        $loader_class = new $class_name;
                        if(method_exists($loader_class, 'install')) {
                            $loader_class->install();
                            $notify .= $engine->template->stringNotify('success', $engine->language->get('extension_install_success'));
                        } else {
                            $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_methodno'));
                        }
                    } else {
                        $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_classno'));
                    }
                } else {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_filenotfound'));
                }
            } else {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('extension_install_always'));
            }
            return $engine->template->assign('notify', $notify, $theme_install);
        } elseif ($this->component_exists()) {
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_components WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $component_back = $engine->constant->root . '/extensions/components/' . $result['dir'] . '/back.php';
            $backend_config = null;
            $com_name = $engine->language->get('admin_component_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_component_'.$result['dir'].'.name');
            if (file_exists($component_back)) {
                $this->object_name = $com_name;
                require_once($component_back);
                $class = "com_{$result['dir']}_back";
                $init = new $class;
                $backend_config = $init->load();
            }
            if ($backend_config == null || strlen($backend_config) < 1) {
                $backend_config = $this->showNull();
            }
            return $backend_config;
        } else {
            // такого компонента нет, отображаем списки
            $theme = $engine->template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
                array($engine->language->get('admin_components_title'), $engine->language->get('admin_components_tab_all'), $engine->language->get('admin_components_tab_enabled'), $engine->language->get('admin_components_tab_dissabled'), $engine->language->get('admin_components_tab_toinstall')),
                $engine->template->get('extension_list'));
            $thead = $engine->template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
                array($engine->language->get('admin_components_table_th_1'), $engine->language->get('admin_components_table_th_2'), $engine->language->get('admin_components_table_th_3'), $engine->language->get('admin_components_table_th_4'),),
                $engine->template->get('extension_thead'));
            $tbody = $engine->template->get('extension_tbody');
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_components");
            $stmt->execute();
            $prepare_theme = array();
            $installed_ext_array = array();
            while ($result = $stmt->fetch()) {
                $config_link = "?object=components&id=" . $result['id'];
                $com_name = $engine->language->get('admin_component_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_component_'.$result['dir'].'.name');
                $com_desc = $engine->language->get('admin_component_'.$result['dir'].'.desc') == null ? $result['dir'] : $engine->language->get('admin_component_'.$result['dir'].'.desc');
                // вносим в список отключенных
                if ($result['enabled'] == 0) {
                    $iconset = $engine->template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id=' . $result['id'], '?object=components&id=' . $result['id'] . '&action=turn'), $engine->template->get('manage_noactive'));
                    $prepare_theme['dissabled'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array($result['id'], $com_name, $com_desc, $iconset),
                        $tbody);
                } // иначе вносим в список включенных
                else {
                    $iconset = $engine->template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id=' . $result['id'], '?object=components&id=' . $result['id'] . '&action=turn'), $engine->template->get('manage_active'));
                    $prepare_theme['enabled'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $com_name, $com_desc, $iconset, $config_link),
                        $tbody);
                }
                $installed_ext_array[] = $result['dir'];
                $iconset = $engine->template->assign('ext_config_link', '?object=components&id=' . $result['id'], $engine->template->get('manage_all'));
                $prepare_theme['all'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                    array($result['id'], $com_name, $com_desc, $iconset, $config_link),
                    $tbody);
            }
            $all_availed_ext = scandir($engine->constant->root . '/extensions/components/');
            foreach($all_availed_ext as $current_ext) {
                if(!$engine->system->prefixEquals($current_ext, '.') && !in_array($current_ext, $installed_ext_array)) {
                    $iconset = $engine->template->assign('ext_turn_link', '?object=components&action=install&add='.$current_ext, $engine->template->get('manage_install'));
                    $prepare_theme['toinstall'] .= $engine->template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array(0, $current_ext, "Uninstalled extension in folder /extension/".$this->object."/".$current_ext, $iconset),
                        $tbody);
                }
            }

            $alllist = $engine->template->assign('extension_tbody', $prepare_theme['all'], $thead);
            $toinstalllist = $engine->template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
            $activelist = $engine->template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
            $noactivelist = $engine->template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
            $theme = $engine->template->assign(
                array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
                array($alllist, $toinstalllist, $activelist, $noactivelist),
                $theme
            );
            return $theme;
        }
    }

    private function showNull()
    {
        global $engine;
        return $engine->template->get('nullcontent');
    }

    private function component_exists()
    {
        global $engine;
        if ($this->id < 1) {
            return false;
        }
        $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_components WHERE id = ?");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result[0] == 1) {
            return true;
        }
        return false;
    }

    private function foreachMenuPositions()
    {
        global $engine;
        $theme = $engine->template->get('header');
        $list_theme = $engine->template->get('list');
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_modules WHERE enabled = 1 LIMIT 5");
        $stmt->execute();
        $module_list = null;
        $component_list = null;
        $hook_list = null;
        while ($result = $stmt->fetch()) {
            $mod_name = $engine->language->get('admin_modules_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_modules_'.$result['dir'].'.name');
            $module_list .= $engine->template->assign(array('list_href', 'list_text'), array("?object=modules&id={$result['id']}", $mod_name), $list_theme);
        }
        $module_list .= $engine->template->assign(array('list_href', 'list_text'), array("?object=modules", $engine->language->get('admin_nav_more_link')), $list_theme);
        $stmt = null;
        $result = null;
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_components WHERE enabled = 1 LIMIT 5");
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $com_name = $engine->language->get('admin_component_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_component_'.$result['dir'].'.name');
            $component_list .= $engine->template->assign(array('list_href', 'list_text'), array("?object=components&id={$result['id']}", $com_name), $list_theme);
        }
        $component_list .= $engine->template->assign(array('list_href', 'list_text'), array("?object=components", $engine->language->get('admin_nav_more_link')), $list_theme);
        $stmt = null;
        $result = null;
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_hooks WHERE enabled = 1 LIMIT 5");
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $hook_name = $engine->language->get('admin_hook_'.$result['dir'].'.name') == null ? $result['dir'] : $engine->language->get('admin_hook_'.$result['dir'].'.name');
            $hook_list .= $engine->template->assign(array('list_href', 'list_text'), array("?object=hooks&id={$result['id']}", $hook_name), $list_theme);
        }
        $hook_list .= $engine->template->assign(array('list_href', 'list_text'), array("?object=hooks", $engine->language->get('admin_nav_more_link')), $list_theme);
        $theme = $engine->template->assign(array('module_list', 'component_list', 'hook_list'), array($module_list, $component_list, $hook_list), $theme);
        return $theme;
    }

    private function loadMainPage()
    {
        global $engine;
        list($month, $day, $year) = explode('-', date('m-d-y'));
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = mktime(0, 0, 0, $month, $day + 1, $year);
        $stmt1 = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
        $stmt1->bindParam(1, $start);
        $stmt1->bindParam(2, $end);
        $stmt1->execute();
        $res1 = $stmt1->fetch();
        $views_count = $res1[0];
        $stmt2 = $engine->database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$engine->constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
        $stmt2->bindParam(1, $start);
        $stmt2->bindParam(2, $end);
        $stmt2->execute();
        $res2 = $stmt2->fetch();
        $unique_user = $res2[0];
        $stmt3 = $engine->database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$engine->constant->db['prefix']}_statistic WHERE time >= ? and time <= ? AND reg_id > 0");
        $stmt3->bindParam(1, $start);
        $stmt3->bindParam(2, $end);
        $stmt3->execute();
        $res3 = $stmt3->fetch();
        $unique_registered = $res3[0];
        $body = $engine->template->get('index_page');
        $engine->template->globalset('view_count', $views_count);
        $engine->template->globalset('user_unique', $unique_user);
        $engine->template->globalset('unique_registered', $unique_registered);
        $engine->template->globalset('unique_unregistered', $unique_user - $unique_registered);
        $engine->template->globalset('date_today', date('d-m-y'));
        $engine->template->globalset('server_os_type', php_uname('s'));
        $engine->template->globalset('server_php_ver', phpversion());
        $engine->template->globalset('server_mysql_ver', $engine->database->con()->getAttribute(PDO::ATTR_SERVER_VERSION));
        $engine->template->globalset('server_load_avg', $this->get_server_load());
        $this->showWeekChart();
        $engine->template->globalset('folder_uploads_access', $this->analiseAccess("/upload/", "rw"));
        $engine->template->globalset('folder_language_access', $this->analiseAccess("/language/", "rw"));
        $engine->template->globalset('folder_cache_access', $this->analiseAccess("/cache/", "rw"));
        $engine->template->globalset('file_config_access', $this->analiseAccess("/config.php", "rw"));
        return $body;
    }

    private function showWeekChart()
    {
        global $engine;
        $json_result = null;
        list($month, $day, $year) = explode('-', date('m-d-y'));
        for ($i = 5; $i >= 0; $i--) {
            $totime = strtotime(date('Y-m-d', time() - ($i * 86400)));
            $fromtime = $totime - (60 * 60 * 24);
            $stmt1 = $engine->database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$engine->constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
            $stmt1->bindParam(1, $fromtime);
            $stmt1->bindParam(2, $totime);
            $stmt1->execute();
            $res1 = $stmt1->fetch();
            $unique_users = $res1[0];
            $stmt2 = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
            $stmt2->bindParam(1, $fromtime);
            $stmt2->bindParam(2, $totime);
            $stmt2->execute();
            $res2 = $stmt2->fetch();
            $view_users = $res2[0];
            $object_date = date('d.m.Y', $fromtime);
            $json_result .= "['{$object_date}', {$view_users}, {$unique_users}],\n";
        }
        $engine->template->globalset('json_chart_result', $json_result);
    }

    private function analiseAccess($data, $rule = 'rw')
    {
        global $engine;
        $error = false;
        for ($i = 0; $i < strlen($rule); $i++) {
            if ($rule[$i] == "r") {
                if (!is_readable($engine->constant->root . $data))
                    $error = true;
            } elseif ($rule[$i] == "w") {
                if (!is_writable($engine->constant->root . $data))
                    $error = true;
            }
        }
        if ($error) {
            return "Error";
        } else {
            return "Ok";
        }
    }

    public function tplSettingsSelectYorN($variable_name, $variable_pseudo_name = null, $variable_desc = null, $selected = false)
    {
        global $engine;
        if ($variable_pseudo_name == null)
            $variable_pseudo_name = $variable_name;
        $selected_yes = null;
        $selected_no = null;
        $selected ? $selected_yes = "selected" : $selected_no = "selected";
        $theme = $engine->template->assign(array('ext_config_name', 'ext_label', 'ext_description', 'selected_yes', 'selected_no'),
            array($variable_name, $variable_pseudo_name, $variable_desc, $selected_yes, $selected_no),
            $engine->template->get('config_block_select_yorn'));
        return $theme;
    }

    public function tplSettingsDirectory($data)
    {
        global $engine;
        return $engine->template->assign('config_directory', $data, $engine->template->get('config_block_spacer'));
    }

    /**
     * Подгрузка блока конфигураций с определенным типом - input.text
     * @param String $variable_name
     * @param String $variable_value
     * @param String $variable_pseudo_name
     * @param String $variable_desc
     * @return mixed
     */
    public function tplSettingsInputText($variable_name, $variable_value = null, $variable_pseudo_name = null, $variable_desc = null)
    {
        global $engine;
        if ($variable_pseudo_name == null)
            $variable_pseudo_name = $variable_name;
        $theme = $engine->template->assign(array('ext_config_name', 'ext_config_value', 'ext_label', 'ext_description'),
            array($variable_name, $variable_value, $variable_pseudo_name, $variable_desc),
            $engine->template->get('config_block_input_text'));
        return $theme;
    }

    /**
     * Быстрая отрисовка таблицы. Параметры должны быть равноценными по сайзу массивами.
     * @param array $columns - названия столбцов, массив. ['Column 1', 'Column 2' ... 'Column n']
     * @param array $tbody - содержимое строк с столбцами array[0] = array(column 1, column 2 ... column n);array[1] = (); ...
     */
    public function tplRawTable($columns, $tbody)
    {
        global $engine;
        if (is_array($columns) && is_array($tbody)) {
            $thead = $engine->template->get('rawtable_thead');
            $th_column = $engine->template->get('rawtable_thcolumn');
            $tbody_tr = $engine->template->get('rawtable_tbody');
            $td_raw_data = $engine->template->get('rawtable_tdcolumn');
            $th_raw_result = null;
            foreach ($columns as $th_data) {
                $th_raw_result .= $engine->template->assign('raw_column', $th_data, $th_column);
            }
            // разбивка всего массива на строки tr
            $full_body_result = null;
            foreach ($tbody as $tr_data) {
                $tr_contains = null;
                // Разбивка 1 строки tr на единичные колонки td
                foreach ($tr_data as $td_data) {
                    $tr_contains .= $engine->template->assign('this_td', $td_data, $td_raw_data);
                }
                $full_body_result .= $engine->template->assign('raw_td', $tr_contains, $tbody_tr);
            }
            return $engine->template->assign(array('raw_th', 'raw_tbody'), array($th_raw_result, $full_body_result), $thead);
        } else {
            return;
        }
    }

    /**
     * Отрисовка быстрой пагинации на страницах админки где она необходима
     * @param unknown_type $list_count
     * @param unknown_type $pagination_page_count
     * @param unknown_type $uri_object
     * @return NULL
     */
    public function tplRawPagination($list_count, $pagination_page_count, $uri_object = "components")
    {
        global $engine;
        $pagination_list_theme = $engine->template->get('list_pagination', 'components/');
        $ret_position = intval($this->getPage() / $list_count);
        $pagination_list = null;
        if ($pagination_page_count <= 10 || $ret_position < 5) {
            for ($i = 0; $i <= $pagination_page_count; $i++) {
                $link_page = $i * $list_count;
                $pagination_list .= $engine->template->assign(
                    array('ext_pagination_href', 'ext_pagination_index'),
                    array('?object=' . $uri_object . '&id=' . $this->id . '&action=list&page=' . $link_page, $i),
                    $pagination_list_theme);
            }
        } else {
            // Наркомания дикая, но работает
            // алгоритм: ----1 ---2 --3 -4 <ret_position> +6 ++7 +++8 ++++9
            $start_ret = $ret_position - 4;
            $end_ret = $ret_position + 4;
            // -3>0
            for (; $start_ret < $ret_position; $start_ret++) {
                $pagination_list .= $engine->template->assign(
                    array('ext_pagination_href', 'ext_pagination_index'),
                    array('?object=' . $uri_object . '&id=' . $this->id . '&action=list&page=' . $start_ret * $list_count, $start_ret),
                    $pagination_list_theme);
            }
            // 0
            $pagination_list .= $engine->template->assign(
                array('ext_pagination_href', 'ext_pagination_index'),
                array('?object=' . $uri_object . '&id=' . $this->id . '&action=list&page=' . $ret_position * $list_count, $ret_position),
                $pagination_list_theme);
            $ret_position++;
            // 0>+3
            for (; $ret_position <= $end_ret; $ret_position++) {
                if ($ret_position <= $pagination_page_count) {
                    $pagination_list .= $engine->template->assign(
                        array('ext_pagination_href', 'ext_pagination_index'),
                        array('?object=' . $uri_object . '&id=' . $this->id . '&action=list&page=' . $ret_position * $list_count, $ret_position),
                        $pagination_list_theme);
                }
            }

        }
        return $pagination_list;
    }

    /**
     * Сохранение конфигов расширения. Обязателен формат: config:name_of_config
     */
    public function trySaveConfigs()
    {
        global $engine;
        // увы, но PHP::PDO не хочет в prepared указывать аргумент имени таблицы :( поэтому ручками
        $table_name = $engine->constant->db['prefix'] . "_";
        switch ($this->object) {
            case "components":
            case "hooks":
            case "modules":
                $table_name .= $this->object;
                break;
        }
        $config_array = array();
        foreach ($engine->system->post(null) as $key => $data) {
            list($config_extension, $config_name) = explode(":", $key);
            if ($config_extension == "config" && strlen($config_name) > 0) {
                $config_array[$config_name] = $data;
            }
        }
        $config_sql = serialize($config_array);
        try {
            $stmt = $engine->database->con()->prepare("UPDATE $table_name SET configs = ? WHERE id = ?");
            $stmt->bindParam(1, $config_sql, PDO::PARAM_STR);
            $stmt->bindParam(2, $this->id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $s) {
            return false;
        }
        $engine->extension->overloadAllExtensionConfigs();
        return true;
    }

    /**
     * Обвертка для выгрузки конфига из расширений
     * @param unknown_type $name
     */
    public function getConfig($name, $var_type = null)
    {
        global $engine;
        return $engine->extension->getConfig($name, $this->id, $this->object, $var_type);
    }

    private function get_server_load()
    {

        if (stristr(PHP_OS, 'win')) {
            return "WIN ERROR";

        } else {
            $sys_load = sys_getloadavg();
            $load = $sys_load[0];
        }
        return $load;
    }

    public function getID()
    {
        return $this->id;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getAdd()
    {
        return $this->add;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getExtName()
    {
        return $this->object_name;
    }

    /**
     * Метод для переключения состояния расширений - модулей, хуков и компонентов
     */
    public function turn()
    {
        global $engine;
        $new_state = 0;
        $modId = $this->getID();
        $table_name = $engine->constant->db['prefix'] . "_" . $this->object;
        $stmt = $engine->database->con()->prepare("SELECT enabled FROM {$table_name} WHERE id = ?");
        $stmt->bindParam(1, $modId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $res['enabled'] == 0 ? $new_state = 1 : $new_state = 0;
        $stmt = null;
        $stmt = $engine->database->con()->prepare("UPDATE {$table_name} SET enabled = ? WHERE id = ?");
        $stmt->bindParam(1, $new_state, PDO::PARAM_INT);
        $stmt->bindParam(2, $modId, PDO::PARAM_INT);
        $stmt->execute();
        $engine->system->redirect($_SERVER['PHP_SELF'] . "?object=" . $this->object);
        return;
    }

}

?>