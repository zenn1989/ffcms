<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class install
{
    public function make()
    {
        global $engine;
        $theme = $engine->template->get('main');
        $work_body = null;
        if($engine->system->get('action') == "install") {
            $work_body = $this->showInstaller();
        } elseif($engine->system->get('action') == "update") {
            $work_body = $this->showUpdater();
        } elseif($engine->system->get('action') == "lang") {
            if(in_array($engine->system->get('lang'), $engine->language->getAvailable())) {
                setcookie('ffcms_lang', $engine->system->get('lang'));
            }
            $engine->system->redirect($_SERVER['PHP_SELF']);
        } else {
            $theme_option_inactive = $engine->template->get('form_option_item_inactive');
            $engine->language->_options = null;
            foreach($engine->language->getAvailable() as $lang_available) {
                $engine->language->_options .= $engine->template->assign(array('option_value', 'option_name'), $lang_available, $theme_option_inactive);
            }
            $work_body = $engine->template->assign('language_options', $engine->language->_options, $engine->template->get('select_action'));
        }
        $theme = $engine->template->assign('body', $work_body, $theme);
        return preg_replace('/{\$(.*?)}/s', '', $engine->language->set($theme));
    }

    private function showInstaller()
    {
        global $engine;
        $theme = $engine->template->get('install');
        $notify = null;
        if(file_exists($engine->constant->root.'/install/.lock')) {
            $theme = $engine->template->stringNotify('error', $engine->language->get('install_locked'));
        } elseif(file_exists($engine->constant->root . '/config.php') && !is_writable($engine->constant->root . '/config.php')) {
            $theme = $engine->template->stringNotify('error', $engine->language->get('install_config_notwritable'));
        } elseif(!is_writable($engine->constant->root . '/install/')) {
            $theme = $engine->template->stringNotify('error', $engine->language->get('install_self_notwritable'));
        } elseif(!file_exists($engine->constant->root . '/install/sql/install.sql')) {
            $theme = $engine->template->stringNotify('error', $engine->language->get('install_sql_not_found'));
        } else {
            if($engine->system->post('submit')) {
                $testCon = null;
                try {
                    $testCon = @new PDO("mysql:host={$engine->system->post('config:db_host')};dbname={$engine->system->post('config:db_name')}", $engine->system->post('config:db_user'), $engine->system->post('config:db_pass'));
                } catch(PDOException $exception) {
                    $testCon = null;
                }
                if($testCon != null) {
                    $reg_notify = null;
                    $reg_login = $engine->system->post('admin:login');
                    $reg_email = $engine->system->post('admin:email');
                    $reg_pass = $engine->system->post('admin:pass');
                    $reg_repass = $engine->system->post('admin:repass');
                    if (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
                        $reg_notify .= $engine->template->stringNotify('error', 'Почта администратора указана не верно');
                    }
                    if (!$engine->system->validPasswordLength($reg_pass)) {
                        $reg_notify .= $engine->template->stringNotify('error', 'Длина пароля администратора не корректна');
                    }
                    if($engine->system->length($reg_login) < 3 || $engine->system->length($reg_login) > 64) {
                        $reg_notify .= $engine->template->stringNotify('error', 'Длина логина администратора не корректна');
                    }
                    if($reg_pass != null && $reg_pass != $reg_repass) {
                        $reg_notify .= $engine->template->stringNotify('error', 'Пароли администратора не совпадают');
                    }
                    if($reg_notify == null) {
                        $configs_data = '<?php'."\n";
                        foreach($engine->system->post_data as $var_name=>$var_value) {
                            if($engine->system->prefixEquals($var_name, 'config:')) {
                                $var_name = str_replace('config:', '', $var_name);
                                $configs_data .= '$config[\''.$var_name.'\'] = "'.$var_value.'"'.";\n";
                            }
                        }
                        $random_password_salt = $engine->system->randomString(rand(12,16));
                        $configs_data .= '$config[\'tpl_dir\'] = "templates";
$config[\'tpl_name\'] = "default";
$config[\'debug\'] = 1;
$config[\'seo_description\'] = "Demonstration website description";
$config[\'seo_keywords\'] = "Website, demo";
$config[\'multi_title\'] = 0;
$config[\'cache_interval\'] = "120";
$config[\'token_time\'] = "86400";
$config[\'mail_from\'] = "admin@example.com";
$config[\'mail_ownername\'] = "Site Admin";
$config[\'mail_smtp_use\'] = 0;
$config[\'mail_smtp_host\'] = "smtp.yandex.ru";
$config[\'mail_smtp_port\'] = "25";
$config[\'mail_smtp_auth\'] = 1;
$config[\'mail_smtp_login\'] = "admin@example.com";
$config[\'mail_smtp_password\'] = "madness";
$config[\'password_salt\'] = "'.$random_password_salt.'";
';
                        $configs_data .= '?>';
                        file_put_contents($engine->constant->root . '/install/.lock', 'Install success');
                        file_put_contents($engine->constant->root . '/config.php', $configs_data);
                        $prefix = $engine->system->post('config:db_prefix');
                        if(!$engine->system->isLatinOrNumeric($prefix)) {
                            $prefix = "ffcms";
                        }
                        $query_dump = $engine->template->assign('db_prefix', $prefix, file_get_contents($engine->constant->root . '/install/sql/install.sql'));
                        $testCon->exec($query_dump);
                        $md5_doublehash = $engine->system->doublemd5($reg_pass, $random_password_salt);
                        $stmt = $testCon->prepare("INSERT INTO {$prefix}_user (`login`, `email`, `nick`, `pass`, `access_level`) VALUES(?, ?, 'admin', ?, '3')");
                        $stmt->bindParam(1, $reg_login, PDO::PARAM_STR);
                        $stmt->bindParam(2, $reg_email, PDO::PARAM_STR);
                        $stmt->bindParam(3, $md5_doublehash, PDO::PARAM_STR, 32);
                        $stmt->execute();
                        $stmt = null;
                        $testCon = null;
                        $notify = $engine->template->stringNotify('success', $engine->language->get('install_done_success'));
                    } else {
                        foreach($engine->system->post_data as $var_name=>$var_value) {
                            if($engine->system->prefixEquals($var_name, 'config:')) {
                                $var_name = str_replace('config:', '', $var_name);
                                $theme = $engine->template->assign('config_'.$var_name, $var_value, $theme);
                            }
                        }
                        $theme = $engine->template->assign(array('admin_login', 'admin_email'), array($engine->system->post('admin:login'), $engine->system->post('admin:email')), $theme);
                        $notify = $reg_notify;
                    }
                } else {
                    foreach($engine->system->post_data as $var_name=>$var_value) {
                        if($engine->system->prefixEquals($var_name, 'config:')) {
                            $var_name = str_replace('config:', '', $var_name);
                            $theme = $engine->template->assign('config_'.$var_name, $var_value, $theme);
                        }
                    }
                    $notify = $engine->template->stringNotify('error', $engine->language->get('install_db_wrongcon'));
                }
            }
            $theme_option_inactive = $engine->template->get('form_option_item_inactive');
            $timezone_option = null;
            $timezone_array = array('Pacific/Kwajalein', 'Pacific/Samoa', 'US/Hawaii', 'US/Alaska', 'US/Pacific', 'US/Arizona', 'America/Mexico_City', 'S/East-Indiana', 'America/Santiago', 'America/Buenos_Aires', 'Brazil/DeNoronha', 'Atlantic/Cape_Verde', 'Europe/London', 'Europe/Berlin', 'Europe/Kiev', 'Europe/Moscow', 'Europe/Samara', 'Asia/Yekaterinburg', 'Asia/Novosibirsk', 'Asia/Krasnoyarsk', 'Asia/Irkutsk', 'Asia/Yakutsk', 'Asia/Vladivostok', 'Asia/Magadan', 'Asia/Kamchatka', 'Pacific/Tongatapu', 'Pacific/Kiritimati');
            foreach($timezone_array as $timezone_current) {
                $timezone_option .= $engine->template->assign(array('option_value', 'option_name'), $timezone_current, $theme_option_inactive);
            }
            $lang_option = null;
            foreach($engine->language->getAvailable() as $lang_current) {
                $lang_option .= $engine->template->assign(array('option_value', 'option_name'), $lang_current, $theme_option_inactive);
            }
            $theme = $engine->template->assign(array('config_option_lang', 'config_option_timezone'), array($lang_option, $timezone_option), $theme);
        }
        return $engine->template->assign('notify', $notify, $theme);
    }

    private function showUpdater()
    {
        global $engine;
        if(!file_exists($engine->constant->root . "/install/.update-".version)) {
            return $engine->template->stringNotify('error', $engine->template->assign('version', version, $engine->language->get('install_update_notify_to_version')));
        }
        $install_log = file_get_contents($engine->constant->root . "/install/.update-".version);
        if($install_log == "locked") {
            return $engine->template->stringNotify('error', $engine->template->assign('version', version, $engine->language->get('install_update_notify_locked')));
        }
        $database = new database();
        $stmt = $engine->database->con()->query("SELECT `version` FROM `{$engine->constant->db['prefix']}_version` LIMIT 1");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $usedVersion = $res['version'];
        $updateQuery = null;
        if($usedVersion === "1.0.0") {
            if(file_exists($engine->constant->root . '/install/sql/update-1.0.0-to-1.1.0.sql')) {
                $updateQuery .= $engine->template->assign('db_prefix', $engine->constant->db['prefix'], file_get_contents($engine->constant->root . '/install/sql/update-1.0.0-to-1.1.0.sql'));
                $updateQuery .= $engine->template->assign('db_prefix', $engine->constant->db['prefix'], file_get_contents($engine->constant->root . '/install/sql/update-1.1.0-to-1.2.0.sql'));
            }
        } elseif($usedVersion === "1.1.0") {
            if(file_exists($engine->constant->root . '/install/sql/update-1.1.0-to-1.2.0.sql'))
                $updateQuery .= $engine->template->assign('db_prefix', $engine->constant->db['prefix'], file_get_contents($engine->constant->root . '/install/sql/update-1.1.0-to-1.2.0.sql'));
        }
        if($updateQuery != null) {
            if($engine->system->post('startupdate')) {
                $engine->database->con()->exec($updateQuery);
                $theme_update = $engine->template->stringNotify('success', $engine->language->get('install_update_success_notify').version);
            } else {
                $theme_update = $engine->template->assign('update_version', version, $engine->template->get('update'));
            }
            return $theme_update;
        } else {
            return $engine->language->get('install_updates_noexist');
        }
    }
}


?>