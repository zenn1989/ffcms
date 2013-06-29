<?php

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

    function __construct()
    {
        global $system;
        $this->object = $system->get('object');
        $this->action = $system->get('action');
        $this->add = $system->get('add');
        $this->id = (int)$system->get('id');
        $this->page = (int)$system->get('page');
    }


    /**
     * Загрузка админ панели. Возвращает скомпилированный вариант вместе с шаблоном.
     */
    public function doload()
    {
        global $page, $user, $template, $system;
        if ($user->get('id') == NULL) {
            $system->redirect('/login');
            exit();
        } elseif ($user->get('access_to_admin') != 1) {
            $system->redirect();
            exit();
        } else {
            switch ($this->object) {
                case "components":
                    $page->setContentPosition('body', $this->loadComponents());
                    break;
                case "modules":
                    $page->setContentPosition('body', $this->loadModules());
                    break;
                case "hooks":
                    $page->setContentPosition('body', $this->loadHooks());
                    break;
                case "settings":
                    $page->setContentPosition('body', $this->loadSystemConfigs());
                    break;
                case "filemanager":
                    $page->setContentPosition('body', $this->loadFileManager());
                    break;
                case "antivirus":
                    $page->setContentPosition('body', $this->loadAntiVirus());
                    break;
                case "dump":
                    $page->setContentPosition('body', $this->loadDumper());
                    break;
                default:
                    $page->setContentPosition('body', $this->loadMainPage());
                    break;
            }
            $template->set('username', $user->get('nick'));
        }
        $header = $this->foreachMenuPositions();
        $page->setContentPosition('header', $header);
        $template->init();
        return $template->compile();
    }

    private function loadDumper()
    {
        global $template, $language, $backup, $system, $constant;
        $action_page_title = $language->get('admin_nav_li_backup') . " : ";
        $menu_theme = $template->tplget('config_menu', null, true);
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=dump&action=export', $language->get('admin_dump_export')), $menu_theme);
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=dump&action=import', $language->get('admin_dump_import')), $menu_theme);
        $work_body = null;
        if ($this->getAction() == "export" || $this->getAction() == null) {
            if ($system->post('submit')){
                $backup->makeDump();
            }
            $action_page_title .= $language->get('admin_dump_export');
            $reader = scandir($constant->root . "/backup/");
            $date_array = array();
            foreach($reader as $files) {
                if(!$system->prefixEquals($files, '.')) {
                    $file_date_array = $system->altexplode('_', $files);
                    $file_date = array_shift($file_date_array);
                    $date_array = $system->arrayAdd($system->toUnixTime($file_date), $date_array);
                }
            }
            arsort($date_array);
            $last_backup_date = null;
            if($date_array[0] == null)
            {
                $last_backup_date = $language->get('admin_dump_notexist');
            }
            else
            {
                $last_backup_date = $system->toDate($date_array[0], 'd');
            }
            $work_body = $template->assign('last_backup_date', $last_backup_date, $template->tplget('backup_export', null, true));
        } elseif ($this->getAction() == "import") {
            $action_page_title .= $language->get('admin_dump_import');
            $scan = scandir($constant->root . "/backup");
            $date_array = array();
            foreach($scan as $file) {
                if(!$system->prefixEquals($file, ".")) {
                    $file_explode = $system->altexplode('_', $file);
                    $file_date = array_shift($file_explode);
                    $date_array = $system->arrayAdd($system->toUnixTime($file_date), $date_array);
                }
            }
            arsort($date_array);
            $raw_prepare_array = array();
            foreach($date_array as $file_date) {
                $read_date = $system->toDate($file_date, 'd');
                $raw_prepare_array[] = array($read_date, '/backup/'.$read_date.'_www.zip', '/backup/'.$read_date.'_sql.sql.gz');
            }
            $raw_table = $this->tplRawTable(array($language->get('admin_dump_th_date'), $language->get('admin_dump_th_www'), $language->get('admin_dump_th_sql')), $raw_prepare_array);
            $work_body = $template->assign('raw_table_files', $raw_table, $template->tplget('backup_import', null, true));
        }
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

    private function loadAntiVirus()
    {
        global $template, $language, $antivirus, $system, $constant;
        $action_page_title = $language->get('admin_nav_li_avir');
        $menu_theme = $template->tplget('config_menu', null, true);
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=antivirus', $language->get('admin_nav_li_avir')), $menu_theme);
        $work_body = $template->tplget('antivirus', null, true);
        $clear_files = null;
        $unknown_files = null;
        $wrong_files = null;
        $hack_files = null;
        if ($system->post('submit') || !file_exists($constant->root . "/cache/.avir_scan")) {
            $antivirus->doFullScan();
            foreach ($antivirus->getClearList() as $file => $md5) {
                $clear_files .= $template->stringNotify('success', $file . " => " . $md5, true);
            }
            foreach ($antivirus->getUnknownList() as $file => $md5) {
                $unknown_files .= $template->stringNotify('warning', $file . " => " . $md5, true);
            }
            foreach ($antivirus->getWrongList() as $file => $md5) {
                $wrong_files .= $template->stringNotify('error', $file . " => " . $md5, true);
            }
            foreach ($antivirus->getHackList() as $file => $md5) {
                $hack_files .= $template->stringNotify('error', $file . " => " . $md5, true);
            }
            if ($hack_files == null) {
                $hack_files = $template->stringNotify('success', $language->get('admin_antivirus_cleaall'));
            }
            if ($wrong_files == null) {
                $wrong_files = $template->stringNotify('success', $language->get('admin_antivirus_cleaall'));
            }
            if ($unknown_files == null) {
                $unknown_files = $template->stringNotify('success', $language->get('admin_antivirus_cleaall'));
            }
            $work_body = $template->assign(array('avir_clear', 'avir_unknown', 'avir_wrong', 'avir_injected'), array($clear_files, $unknown_files, $wrong_files, $hack_files), $work_body);
            file_put_contents($constant->root . "/cache/.avir_scan", $work_body);
        } else {
            $work_body = file_get_contents($constant->root . "/cache/.avir_scan");
        }
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

    private function loadFileManager()
    {
        global $template, $language;
        $action_page_title = $language->get('admin_nav_li_filemanager');
        $menu_theme = $template->tplget('config_menu', null, true);
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=filemanager', $language->get('admin_nav_li_filemanager')), $menu_theme);
        $work_body = $template->tplget('file_manager', null, true);
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

    private function loadSystemConfigs()
    {
        global $template, $language, $config, $constant, $system;
        if ($system->post('submit')) {
            $save_data = "<?php\n";
            foreach ($system->post_data as $var_name => $var_value) {
                if ($system->prefixEquals($var_name, 'cfgmain:')) {
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
            if (is_readable($constant->root . $constant->ds . "config.php") && is_writable($constant->root . $constant->ds . "config.php")) {
                file_put_contents($constant->root . $constant->ds . "config.php", $save_data);
            }
            $system->redirect($_SERVER['PHP_SELF'] . "?object=settings&action=saved");
        }
        $action_page_title = $language->get('admin_settings_title');
        $menu_theme = $template->tplget('config_menu', null, true);
        $menu_link = null;
        $menu_link .= $template->assign(array('ext_menu_link', 'ext_menu_text'), array('?object=settings', $language->get('admin_nav_li_settings')), $menu_theme);

        $work_body = $template->tplget('settings', null, true);
        $theme_option_active = $template->tplget('form_option_item_active', null, true);
        $theme_option_inactive = $template->tplget('form_option_item_inactive', null, true);
        // строчные конфигурации, редактируемые вручную через input.text
        foreach ($config as $cfg_name => $cfg_value) {
            if ($cfg_name == 'tpl_name') {
                $theme_scan_option = null;
                $scan = scandir($constant->root . $constant->ds . $constant->tpl_dir);
                foreach ($scan as $found_tpl) {
                    if ($found_tpl != '.' && $found_tpl != '..' && !$system->contains('.', $found_tpl) && !$system->contains('admin', $found_tpl)) {
                        if ($found_tpl == $constant->tpl_name) {
                            $theme_scan_option .= $template->assign(array('option_value', 'option_name'), $found_tpl, $theme_option_active);
                        } else {
                            $theme_scan_option .= $template->assign(array('option_value', 'option_name'), $found_tpl, $theme_option_inactive);
                        }
                    }
                }
                $work_body = $template->assign('settings_tpl_name_list', $theme_scan_option, $work_body);
            } elseif ($cfg_name == 'debug') {
                $theme_debug_option = null;
                if ($cfg_value == true) {
                    $theme_debug_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_active);
                    $theme_debug_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_debug_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_debug_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $template->assign('settings_debug_list', $theme_debug_option, $work_body);
            } elseif ($cfg_name == 'multi_title') {
                $theme_multititle_option = null;
                if ($cfg_value == true) {
                    $theme_multititle_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_active);
                    $theme_multititle_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_multititle_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_multititle_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $template->assign('settings_multi_title_list', $theme_multititle_option, $work_body);
            } elseif ($cfg_name == "mail_smtp_use") {
                $theme_smtpuse_option = null;
                if ($cfg_value == true) {
                    $theme_smtpuse_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_active);
                    $theme_smtpuse_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_smtpuse_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_smtpuse_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $template->assign('settings_smtp_use_list', $theme_smtpuse_option, $work_body);
            } elseif ($cfg_name == "mail_smtp_auth") {
                $theme_smtpauth_option = null;
                if ($cfg_value == true) {
                    $theme_smtpauth_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_active);
                    $theme_smtpauth_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_inactive);
                } else {
                    $theme_smtpauth_option .= $template->assign(array('option_value', 'option_name'), array(0, $language->get('admin_settings_isoff')), $theme_option_active);
                    $theme_smtpauth_option .= $template->assign(array('option_value', 'option_name'), array(1, $language->get('admin_settings_ison')), $theme_option_inactive);
                }
                $work_body = $template->assign('settings_smtp_auth_list', $theme_smtpauth_option, $work_body);
            }
            if (!is_bool($cfg_value))
                $work_body = $template->assign('config.' . $cfg_name, $cfg_value, $work_body);
        }
        if ($this->getAction() == "saved") {
            $work_body = $template->assign('notify', $template->stringNotify('success', $language->get('admin_settings_saved')), $work_body);
        }
        $body_form = $template->assign(array('ext_configs', 'ext_menu', 'ext_action_title'), array($work_body, $menu_link, $action_page_title), $template->tplget('config_head', null, true));
        return $body_form;
    }

    private function loadHooks()
    {
        global $template, $language, $database, $constant;
        if ($this->hook_exists()) {
            // хук существует, обращаемся к настройке
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_hooks WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $component_back = $constant->root . '/extensions/hooks/' . $result['dir'] . '/back.php';
            $backend_config = null;
            if (file_exists($component_back)) {
                $this->object_name = $result['name'];
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
            $theme = $template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
                array($language->get('admin_hooks_title'), $language->get('admin_hooks_tab_all'), $language->get('admin_hooks_tab_enabled'), $language->get('admin_hooks_tab_dissabled'), $language->get('admin_hooks_tab_toinstall')),
                $template->tplget('extension_list', null, true));
            $thead = $template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
                array($language->get('admin_hooks_table_th_1'), $language->get('admin_hooks_table_th_2'), $language->get('admin_hooks_table_th_3'), $language->get('admin_hooks_table_th_4'),),
                $template->tplget('extension_thead', null, true));
            $tbody = $template->tplget('extension_tbody', null, true);
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_hooks");
            $stmt->execute();
            $prepare_theme = array();
            while ($result = $stmt->fetch()) {
                $config_link = "?object=hooks&id=" . $result['id'];
                // вносим в список отключенных
                if ($result['enabled'] == 0) {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array($config_link, '?object=hooks&id=' . $result['id'] . '&action=turn'), $template->tplget('manage_noactive', null, true));
                    $prepare_theme['dissabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                        $tbody);
                } // иначе вносим в список включенных
                else {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=hooks&id=' . $result['id'], '?object=hooks&id=' . $result['id'] . '&action=turn'), $template->tplget('manage_active', null, true));
                    $prepare_theme['enabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                        $tbody);
                }
                // вносим в список не установленных
                if ($result['installed'] == 0) {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=hooks&id=' . $result['id'], '?object=hooks&id=' . $result['id'] . '&action=install'), $template->tplget('manage_install', null, true));
                    $prepare_theme['toinstall'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array($result['id'], $result['name'], $result['description'], $iconset),
                        $tbody);
                }
                $iconset = $template->assign('ext_config_link', '?object=hooks&id=' . $result['id'], $template->tplget('manage_all', null, true));
                $prepare_theme['all'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                    array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                    $tbody);
            }
            $alllist = $template->assign('extension_tbody', $prepare_theme['all'], $thead);
            $toinstalllist = $template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
            $activelist = $template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
            $noactivelist = $template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
            $theme = $template->assign(
                array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
                array($alllist, $toinstalllist, $activelist, $noactivelist),
                $theme
            );
            return $theme;

        }
    }

    private function hook_exists()
    {
        global $database, $constant;
        if ($this->id < 1) {
            return false;
        }
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_hooks WHERE id = ?");
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
        global $template, $language, $database, $constant;
        if ($this->module_exits()) {
            // модуль существует, выгружаем backend
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_modules WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $component_back = $constant->root . '/extensions/modules/' . $result['dir'] . '/back.php';
            $backend_config = null;
            if (file_exists($component_back)) {
                $this->object_name = $result['name'];
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
            $theme = $template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
                array($language->get('admin_modules_title'), $language->get('admin_modules_tab_all'), $language->get('admin_modules_tab_enabled'), $language->get('admin_modules_tab_dissabled'), $language->get('admin_modules_tab_toinstall')),
                $template->tplget('extension_list', null, true));
            $thead = $template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
                array($language->get('admin_modules_table_th_1'), $language->get('admin_modules_table_th_2'), $language->get('admin_modules_table_th_3'), $language->get('admin_modules_table_th_4'),),
                $template->tplget('extension_thead', null, true));
            $tbody = $template->tplget('extension_tbody', null, true);
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_modules");
            $stmt->execute();
            $prepare_theme = array();
            while ($result = $stmt->fetch()) {
                $config_link = "?object=modules&id=" . $result['id'];
                // вносим в список отключенных
                if ($result['enabled'] == 0) {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array($config_link, '?object=modules&id=' . $result['id'] . '&action=turn'), $template->tplget('manage_noactive', null, true));
                    $prepare_theme['dissabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                        $tbody);
                } // иначе вносим в список включенных
                else {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=modules&id=' . $result['id'], '?object=modules&id=' . $result['id'] . '&action=turn'), $template->tplget('manage_active', null, true));
                    $prepare_theme['enabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                        $tbody);
                }
                // вносим в список не установленных
                if ($result['installed'] == 0) {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=modules&id=' . $result['id'], '?object=modules&id=' . $result['id'] . '&action=install'), $template->tplget('manage_install', null, true));
                    $prepare_theme['toinstall'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array($result['id'], $result['name'], $result['description'], $iconset),
                        $tbody);
                }
                $iconset = $template->assign('ext_config_link', '?object=modules&id=' . $result['id'], $template->tplget('manage_all', null, true));
                $prepare_theme['all'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                    array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                    $tbody);
            }
            $alllist = $template->assign('extension_tbody', $prepare_theme['all'], $thead);
            $toinstalllist = $template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
            $activelist = $template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
            $noactivelist = $template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
            $theme = $template->assign(
                array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
                array($alllist, $toinstalllist, $activelist, $noactivelist),
                $theme
            );
            return $theme;
        }
    }

    private function module_exits()
    {
        global $database, $constant;
        if ($this->id < 1) {
            return false;
        }
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_modules WHERE id = ?");
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
        global $template, $language, $database, $constant;
        if ($this->component_exists()) {
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_components WHERE id = ?");
            $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $component_back = $constant->root . '/extensions/components/' . $result['dir'] . '/back.php';
            $backend_config = null;
            if (file_exists($component_back)) {
                $this->object_name = $result['name'];
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
            $theme = $template->assign(array('title', 'word_all', 'word_active', 'word_noactive', 'word_toinstall'),
                array($language->get('admin_components_title'), $language->get('admin_components_tab_all'), $language->get('admin_components_tab_enabled'), $language->get('admin_components_tab_dissabled'), $language->get('admin_components_tab_toinstall')),
                $template->tplget('extension_list', null, true));
            $thead = $template->assign(array('ext_th_1', 'ext_th_2', 'ext_th_3', 'ext_th_4'),
                array($language->get('admin_components_table_th_1'), $language->get('admin_components_table_th_2'), $language->get('admin_components_table_th_3'), $language->get('admin_components_table_th_4'),),
                $template->tplget('extension_thead', null, true));
            $tbody = $template->tplget('extension_tbody', null, true);
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_components");
            $stmt->execute();
            $prepare_theme = array();
            while ($result = $stmt->fetch()) {
                $config_link = "?object=components&id=" . $result['id'];
                // вносим в список отключенных
                if ($result['enabled'] == 0) {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id=' . $result['id'], '?object=components&id=' . $result['id'] . '&action=turn'), $template->tplget('manage_noactive', null, true));
                    $prepare_theme['dissabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array($result['id'], $result['name'], $result['description'], $iconset),
                        $tbody);
                } // иначе вносим в список включенных
                else {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id=' . $result['id'], '?object=components&id=' . $result['id'] . '&action=turn'), $template->tplget('manage_active', null, true));
                    $prepare_theme['enabled'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                        array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                        $tbody);
                }
                // вносим в список не установленных
                if ($result['installed'] == 0) {
                    $iconset = $template->assign(array('ext_config_link', 'ext_turn_link'), array('?object=components&id=' . $result['id'], '?object=components&id=' . $result['id'] . '&action=install'), $template->tplget('manage_install', null, true));
                    $prepare_theme['toinstall'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage'),
                        array($result['id'], $result['name'], $result['description'], $iconset),
                        $tbody);
                }
                $iconset = $template->assign('ext_config_link', '?object=components&id=' . $result['id'], $template->tplget('manage_all', null, true));
                $prepare_theme['all'] .= $template->assign(array('ext_id', 'ext_name', 'ext_desc', 'ext_manage', 'ext_config_link'),
                    array($result['id'], $result['name'], $result['description'], $iconset, $config_link),
                    $tbody);
            }
            $alllist = $template->assign('extension_tbody', $prepare_theme['all'], $thead);
            $toinstalllist = $template->assign('extension_tbody', $prepare_theme['toinstall'], $thead);
            $activelist = $template->assign('extension_tbody', $prepare_theme['enabled'], $thead);
            $noactivelist = $template->assign('extension_tbody', $prepare_theme['dissabled'], $thead);
            $theme = $template->assign(
                array('all_list', 'toinstall_list', 'active_list', 'notactive_list'),
                array($alllist, $toinstalllist, $activelist, $noactivelist),
                $theme
            );
            return $theme;
        }
    }

    private function showNull()
    {
        global $template;
        return $template->tplget('nullcontent', null, true);
    }

    private function component_exists()
    {
        global $database, $constant;
        if ($this->id < 1) {
            return false;
        }
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_components WHERE id = ?");
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
        global $template, $database, $constant, $language;
        $theme = $template->tplget('header', null, true);
        $list_theme = $template->tplget('list', null, true);
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_modules WHERE enabled = 1 LIMIT 5");
        $stmt->execute();
        $module_list = null;
        $component_list = null;
        $hook_list = null;
        while ($result = $stmt->fetch()) {
            $module_list .= $template->assign(array('list_href', 'list_text'), array("?object=modules&id={$result['id']}", $result['name']), $list_theme);
        }
        $module_list .= $template->assign(array('list_href', 'list_text'), array("?object=modules", $language->get('admin_nav_more_link')), $list_theme);
        $stmt = null;
        $result = null;
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_components WHERE enabled = 1 LIMIT 5");
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $component_list .= $template->assign(array('list_href', 'list_text'), array("?object=components&id={$result['id']}", $result['name']), $list_theme);
        }
        $component_list .= $template->assign(array('list_href', 'list_text'), array("?object=components", $language->get('admin_nav_more_link')), $list_theme);
        $stmt = null;
        $result = null;
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_hooks WHERE enabled = 1 LIMIT 5");
        $stmt->execute();
        while ($result = $stmt->fetch()) {
            $hook_list .= $template->assign(array('list_href', 'list_text'), array("?object=hooks&id={$result['id']}", $result['name']), $list_theme);
        }
        $hook_list .= $template->assign(array('list_href', 'list_text'), array("?object=hooks", $language->get('admin_nav_more_link')), $list_theme);
        $theme = $template->assign(array('module_list', 'component_list', 'hook_list'), array($module_list, $component_list, $hook_list), $theme);
        return $theme;
    }

    private function loadMainPage()
    {
        global $database, $constant, $template;
        list($month, $day, $year) = explode('-', date('m-d-y'));
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = mktime(0, 0, 0, $month, $day + 1, $year);
        $stmt1 = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
        $stmt1->bindParam(1, $start);
        $stmt1->bindParam(2, $end);
        $stmt1->execute();
        $res1 = $stmt1->fetch();
        $views_count = $res1[0];
        $stmt2 = $database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
        $stmt2->bindParam(1, $start);
        $stmt2->bindParam(2, $end);
        $stmt2->execute();
        $res2 = $stmt2->fetch();
        $unique_user = $res2[0];
        $stmt3 = $database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$constant->db['prefix']}_statistic WHERE time >= ? and time <= ? AND reg_id > 0");
        $stmt3->bindParam(1, $start);
        $stmt3->bindParam(2, $end);
        $stmt3->execute();
        $res3 = $stmt3->fetch();
        $unique_registered = $res3[0];
        $body = $template->tplget('index_page', null, true);
        $template->globalset('view_count', $views_count);
        $template->globalset('user_unique', $unique_user);
        $template->globalset('unique_registered', $unique_registered);
        $template->globalset('unique_unregistered', $unique_user - $unique_registered);
        $template->globalset('date_today', date('d-m-y'));
        $template->globalset('server_os_type', php_uname('s'));
        $template->globalset('server_php_ver', phpversion());
        $template->globalset('server_mysql_ver', $database->con()->getAttribute(PDO::ATTR_SERVER_VERSION));
        $template->globalset('server_load_avg', $this->get_server_load());
        $this->showWeekChart();
        $template->globalset('folder_uploads_access', $this->analiseAccess("/upload/", "rw"));
        $template->globalset('folder_language_access', $this->analiseAccess("/language/", "rw"));
        $template->globalset('folder_cache_access', $this->analiseAccess("/cache/", "rw"));
        $template->globalset('file_config_access', $this->analiseAccess("/config.php", "rw"));
        return $body;
    }

    private function showWeekChart()
    {
        global $database, $constant, $template, $language;
        $json_result = null;
        list($month, $day, $year) = explode('-', date('m-d-y'));
        for ($i = 5; $i >= 0; $i--) {
            $totime = strtotime(date('Y-m-d', time() - ($i * 86400)));
            $fromtime = $totime - (60 * 60 * 24);
            $stmt1 = $database->con()->prepare("SELECT COUNT(DISTINCT ip,cookie) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
            $stmt1->bindParam(1, $fromtime);
            $stmt1->bindParam(2, $totime);
            $stmt1->execute();
            $res1 = $stmt1->fetch();
            $unique_users = $res1[0];
            $stmt2 = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_statistic WHERE time >= ? AND time <= ?");
            $stmt2->bindParam(1, $fromtime);
            $stmt2->bindParam(2, $totime);
            $stmt2->execute();
            $res2 = $stmt2->fetch();
            $view_users = $res2[0];
            $object_date = date('d.m.Y', $fromtime);
            $json_result .= "['{$object_date}', {$view_users}, {$unique_users}],\n";
        }
        $template->globalset('json_chart_result', $json_result);
    }

    private function analiseAccess($data, $rule = 'rw')
    {
        global $constant;
        $error = false;
        for ($i = 0; $i < strlen($rule); $i++) {
            if ($rule[$i] == "r") {
                if (!is_readable($constant->root . $data))
                    $error = true;
            } elseif ($rule[$i] == "w") {
                if (!is_writable($constant->root . $data))
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
        global $template;
        if ($variable_pseudo_name == null)
            $variable_pseudo_name = $variable_name;
        $selected_yes = null;
        $selected_no = null;
        $selected ? $selected_yes = "selected" : $selected_no = "selected";
        $theme = $template->assign(array('ext_config_name', 'ext_label', 'ext_description', 'selected_yes', 'selected_no'),
            array($variable_name, $variable_pseudo_name, $variable_desc, $selected_yes, $selected_no),
            $template->tplget('config_block_select_yorn', null, true));
        return $theme;
    }

    public function tplSettingsDirectory($data)
    {
        global $template;
        return $template->assign('config_directory', $data, $template->tplget('config_block_spacer', null, true));
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
        global $template;
        if ($variable_pseudo_name == null)
            $variable_pseudo_name = $variable_name;
        $theme = $template->assign(array('ext_config_name', 'ext_config_value', 'ext_label', 'ext_description'),
            array($variable_name, $variable_value, $variable_pseudo_name, $variable_desc),
            $template->tplget('config_block_input_text', null, true));
        return $theme;
    }

    /**
     * Быстрая отрисовка таблицы. Параметры должны быть равноценными по сайзу массивами.
     * @param array $columns - названия столбцов, массив. ['Column 1', 'Column 2' ... 'Column n']
     * @param array $tbody - содержимое строк с столбцами array[0] = array(column 1, column 2 ... column n);array[1] = (); ...
     */
    public function tplRawTable($columns, $tbody)
    {
        global $template, $language;
        if (is_array($columns) && is_array($tbody)) {
            $thead = $template->tplget('rawtable_thead', null, true);
            $th_column = $template->tplget('rawtable_thcolumn', null, true);
            $tbody_tr = $template->tplget('rawtable_tbody', null, true);
            $td_raw_data = $template->tplget('rawtable_tdcolumn', null, true);
            $th_raw_result = null;
            foreach ($columns as $th_data) {
                $th_raw_result .= $template->assign('raw_column', $th_data, $th_column);
            }
            // разбивка всего массива на строки tr
            $full_body_result = null;
            foreach ($tbody as $tr_data) {
                $tr_contains = null;
                // Разбивка 1 строки tr на единичные колонки td
                foreach ($tr_data as $td_data) {
                    $tr_contains .= $template->assign('this_td', $td_data, $td_raw_data);
                }
                $full_body_result .= $template->assign('raw_td', $tr_contains, $tbody_tr);
            }
            return $template->assign(array('raw_th', 'raw_tbody'), array($th_raw_result, $full_body_result), $thead);
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
        global $template;
        $pagination_list_theme = $template->tplget('list_pagination', 'components/', true);
        $ret_position = intval($this->page / $list_count);
        $pagination_list = null;
        if ($pagination_page_count <= 10 || $ret_position < 5) {
            for ($i = 0; $i <= $pagination_page_count; $i++) {
                $link_page = $i * $list_count;
                $pagination_list .= $template->assign(
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
                $pagination_list .= $template->assign(
                    array('ext_pagination_href', 'ext_pagination_index'),
                    array('?object=' . $uri_object . '&id=' . $this->id . '&action=list&page=' . $start_ret * $list_count, $start_ret),
                    $pagination_list_theme);
            }
            // 0
            $pagination_list .= $template->assign(
                array('ext_pagination_href', 'ext_pagination_index'),
                array('?object=' . $uri_object . '&id=' . $this->id . '&action=list&page=' . $ret_position * $list_count, $ret_position),
                $pagination_list_theme);
            $ret_position++;
            // 0>+3
            for (; $ret_position <= $end_ret; $ret_position++) {
                if ($ret_position <= $pagination_page_count) {
                    $pagination_list .= $template->assign(
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
        global $system, $database, $constant;
        // увы, но PHP::PDO не хочет в prepared указывать аргумент имени таблицы :( поэтому ручками
        $table_name = $constant->db['prefix'] . "_";
        switch ($this->object) {
            case "components":
            case "hooks":
            case "modules":
                $table_name .= $this->object;
                break;
        }
        $config_array = array();
        foreach ($system->post(null) as $key => $data) {
            list($config_extension, $config_name) = explode(":", $key);
            if ($config_extension == "config" && strlen($config_name) > 0) {
                $config_array[$config_name] = $data;
            }
        }
        $config_sql = serialize($config_array);
        try {
            $stmt = $database->con()->prepare("UPDATE $table_name SET configs = ? WHERE id = ?");
            $stmt->bindParam(1, $config_sql, PDO::PARAM_STR);
            $stmt->bindParam(2, $this->id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $s) {
            return FALSE;
        }
        return true;
    }

    /**
     * Обвертка для выгрузки конфига из расширений
     * @param unknown_type $name
     */
    public function getConfig($name, $var_type = null)
    {
        global $extension;
        return $extension->getConfig($name, $this->id, $this->object, $var_type);
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
        global $database, $constant, $system;
        $new_state = 0;
        $modId = $this->getID();
        $table_name = $constant->db['prefix'] . "_" . $this->object;
        $stmt = $database->con()->prepare("SELECT enabled FROM {$table_name} WHERE id = ?");
        $stmt->bindParam(1, $modId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $res['enabled'] == 0 ? $new_state = 1 : $new_state = 0;
        $stmt = null;
        $stmt = $database->con()->prepare("UPDATE {$table_name} SET enabled = ? WHERE id = ?");
        $stmt->bindParam(1, $new_state, PDO::PARAM_INT);
        $stmt->bindParam(2, $modId, PDO::PARAM_INT);
        $stmt->execute();
        $system->redirect($_SERVER['PHP_SELF'] . "?object=" . $this->object);
        return;
    }

}

?>