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
        global $language, $template, $system;
        $theme = $template->get('main');
        $work_body = null;
        if($system->get('action') == "install") {
            $work_body = $this->showInstaller();
        } elseif($system->get('action') == "update") {
            $work_body = $this->showUpdater();
        } elseif($system->get('action') == "lang") {
            if(in_array($system->get('lang'), $language->getAvailable())) {
                setcookie('ffcms_lang', $system->get('lang'));
            }
            $system->redirect($_SERVER['PHP_SELF']);
        } else {
            $theme_option_inactive = $template->get('form_option_item_inactive');
            $language_options = null;
            foreach($language->getAvailable() as $lang_available) {
                $language_options .= $template->assign(array('option_value', 'option_name'), $lang_available, $theme_option_inactive);
            }
            $work_body = $template->assign('language_options', $language_options, $template->get('select_action'));
        }
        $theme = $template->assign('body', $work_body, $theme);
        return preg_replace('/{\$(.*?)}/s', '', $language->set($theme));
    }

    private function showInstaller()
    {
        global $template, $constant, $system, $language;
        $theme = $template->get('install');
        $notify = null;
        if(file_exists($constant->root.'/install/.lock')) {
            $theme = $template->stringNotify('error', $language->get('install_locked'));
        } elseif(!is_writable($constant->root . '/config.php')) {
            $theme = $template->stringNotify('error', $language->get('install_config_notwritable'));
        } elseif(!is_writable($constant->root . '/install/')) {
            $theme = $template->stringNotify('error', $language->get('install_self_notwritable'));
        } elseif(!file_exists($constant->root . '/install/sql/install.sql')) {
            $theme = $template->stringNotify('error', $language->get('install_sql_not_found'));
        } else {
            if($system->post('submit')) {
                $testCon = null;
                try {
                    $testCon = @new PDO("mysql:host={$system->post('config:db_host')};dbname={$system->post('config:db_name')}", $system->post('config:db_user'), $system->post('config:db_pass'));
                } catch(PDOException $exception) {
                    $testCon = null;
                }
                if($testCon != null) {
                    $reg_notify = null;
                    $reg_login = $system->post('admin:login');
                    $reg_email = $system->post('admin:email');
                    $reg_pass = $system->post('admin:pass');
                    $reg_repass = $system->post('admin:repass');
                    if (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
                        $reg_notify .= $template->stringNotify('error', 'Почта администратора указана не верно');
                    }
                    if (!$system->validPasswordLength($reg_pass)) {
                        $reg_notify .= $template->stringNotify('error', 'Длина пароля администратора не корректна');
                    }
                    if($system->length($reg_login) < 3 || $system->length($reg_login) > 64) {
                        $reg_notify .= $template->stringNotify('error', 'Длина логина администратора не корректна');
                    }
                    if($reg_pass != null && $reg_pass != $reg_repass) {
                        $reg_notify .= $template->stringNotify('error', 'Пароли администратора не совпадают');
                    }
                    if($reg_notify == null) {
                        $configs_data = '<?php'."\n";
                        foreach($system->post_data as $var_name=>$var_value) {
                            if($system->prefixEquals($var_name, 'config:')) {
                                $var_name = str_replace('config:', '', $var_name);
                                $configs_data .= '$config[\''.$var_name.'\'] = "'.$var_value.'"'.";\n";
                            }
                        }
                        $random_password_salt = $system->randomString(rand(12,16));
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
                        file_put_contents($constant->root . '/install/.lock', 'Install success');
                        file_put_contents($constant->root . '/config.php', $configs_data);
                        $prefix = $system->post('config:db_prefix');
                        if(!$system->isLatinOrNumeric($prefix)) {
                            $prefix = "ffcms";
                        }
                        $query_dump = $template->assign('db_prefix', $prefix, file_get_contents($constant->root . '/install/sql/install.sql'));
                        $testCon->exec($query_dump);
                        // $testCon->multiquery();
                        $md5_doublehash = $system->doublemd5($reg_pass, $random_password_salt);
                        $stmt = $testCon->prepare("INSERT INTO {$prefix}_user (`login`, `email`, `nick`, `pass`, `access_level`) VALUES(?, ?, 'admin', ?, '3')");
                        $stmt->bindParam(1, $reg_login, PDO::PARAM_STR);
                        $stmt->bindParam(2, $reg_email, PDO::PARAM_STR);
                        $stmt->bindParam(3, $md5_doublehash, PDO::PARAM_STR, 32);
                        $stmt->execute();
                        $stmt = null;
                        $testCon = null;
                        $notify = $template->stringNotify('success', $language->get('install_done_success'));
                    } else {
                        foreach($system->post_data as $var_name=>$var_value) {
                            if($system->prefixEquals($var_name, 'config:')) {
                                $var_name = str_replace('config:', '', $var_name);
                                $theme = $template->assign('config_'.$var_name, $var_value, $theme);
                            }
                        }
                        $theme = $template->assign(array('admin_login', 'admin_email'), array($system->post('admin:login'), $system->post('admin:email')), $theme);
                        $notify = $reg_notify;
                    }
                } else {
                    foreach($system->post_data as $var_name=>$var_value) {
                        if($system->prefixEquals($var_name, 'config:')) {
                            $var_name = str_replace('config:', '', $var_name);
                            $theme = $template->assign('config_'.$var_name, $var_value, $theme);
                        }
                    }
                    $notify = $template->stringNotify('error', $language->get('install_db_wrongcon'));
                }
            }
            $theme_option_inactive = $template->get('form_option_item_inactive');
            $timezone_option = null;
            $timezone_array = array('Pacific/Kwajalein', 'Pacific/Samoa', 'US/Hawaii', 'US/Alaska', 'US/Pacific', 'US/Arizona', 'America/Mexico_City', 'S/East-Indiana', 'America/Santiago', 'America/Buenos_Aires', 'Brazil/DeNoronha', 'Atlantic/Cape_Verde', 'Europe/London', 'Europe/Berlin', 'Europe/Kiev', 'Europe/Moscow', 'Europe/Samara', 'Asia/Yekaterinburg', 'Asia/Novosibirsk', 'Asia/Krasnoyarsk', 'Asia/Irkutsk', 'Asia/Yakutsk', 'Asia/Vladivostok', 'Asia/Magadan', 'Asia/Kamchatka', 'Pacific/Tongatapu', 'Pacific/Kiritimati');
            foreach($timezone_array as $timezone_current) {
                $timezone_option .= $template->assign(array('option_value', 'option_name'), $timezone_current, $theme_option_inactive);
            }
            $lang_option = null;
            foreach($language->getAvailable() as $lang_current) {
                $lang_option .= $template->assign(array('option_value', 'option_name'), $lang_current, $theme_option_inactive);
            }
            $theme = $template->assign(array('config_option_lang', 'config_option_timezone'), array($lang_option, $timezone_option), $theme);
        }
        return $template->assign('notify', $notify, $theme);
    }

    private function showUpdater()
    {
        global $language;
        return $language->get('install_updates_noexist');
    }
}


?>