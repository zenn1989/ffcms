<?php
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
        } else {
            $work_body = $template->get('select_action');
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
        } elseif(!is_writable($constant->root . '/config.php'))
        {
            $theme = $template->stringNotify('error', $language->get('install_config_notwritable'));
        } elseif(!is_writable($constant->root . '/install/')) {
            $theme = $template->stringNotify('error', $language->get('install_self_notwritable'));
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
';
                        $configs_data .= '?>';
                        file_put_contents($constant->root . '/install/.lock', 'Install success');
                        file_put_contents($constant->root . '/config.example.php', $configs_data);
                        // $testCon->multiquery();
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