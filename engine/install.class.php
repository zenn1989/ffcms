<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class install extends singleton {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        template::getInstance()->set(template::TYPE_SYSTEM, 'lang_available', language::getInstance()->getAvailable());
        $content = null;
        switch(system::getInstance()->get('action')) {
            case 'check':
                $content = $this->viewCheck();
                break;
            case 'install':
                $content = $this->viewInstall();
                break;
            case 'update':
                $content = $this->viewUpdate();
                break;
            case 'changelanguage':
                $content = $this->viewChangeLang();
                break;
            case null:
                $content = $this->viewMain();
                break;
            default:
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewCheck() {
        $params = array();

        if(file_exists(root.'/install/.lock')) {
            $params['notify']['prepare']['lock'] = true;
        }
        if(file_exists(root . '/config.php') && !is_writable(root . '/config.php')) {
            $params['notify']['prepare']['cfg_write'] = true;
        }
        if(!is_writable(root . '/install/')) {
            $params['notify']['prepare']['inst_write'] = true;
        }
        if(!file_exists(root . '/install/sql/install.sql')) {
            $params['notify']['prepare']['sql_notfound'] = true;
        }
        if(!file_exists(root . '/install/.install')) {
            $params['notify']['prepare']['inst_unlock'] = true;
        }

        if(sizeof($params['notify']) == 0) {
            $check_all = false;
            $php_version = phpversion();
            $php_v_array = system::getInstance()->altexplode(".", $php_version);
            $php_v_check = false;
            if($php_v_array[0] * 10000 + $php_v_array[1] * 100 + $php_v_array[2] >= 50302) // 5.3.2 or more
                $php_v_check = true;

            $mod_rewrite = false;
            if(function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))
                $mod_rewrite = true;
            elseif(isset($_SERVER['IIS_UrlRewriteModule']))
                $mod_rewrite = true;

            $params['compare']['php_version'] = $php_version;
            $params['compare']['php_check'] = $php_v_check;
            $params['compare']['php_pdo'] = extension_loaded('pdo');
            $params['compare']['apache_rewrite'] = $mod_rewrite;
            $params['compare']['php_gd'] = extension_loaded('gd') && function_exists('gd_info');

            if($params['compare']['php_check'] === true && $params['compare']['php_pdo'] === true
                && $params['compare']['apache_rewrite'] && $params['compare']['php_gd'])
                $check_all = true;

            $params['compare']['all_ok'] = $check_all;
        }

        return template::getInstance()->twigRender('check.tpl', $params);
    }

    private function viewUpdate() {
        $params = array();
        if(!file_exists(root . "/install/.update-".version)) {
            $params['notify']['unlock_update'] = true;
        }
        $install_log = @file_get_contents(root . "/install/.update-".version);
        if($install_log == "locked") {
            $params['notify']['locked_update'] = true;
        }
        if(!$this->isInstalled()) {
            $params['notify']['not_installed'] = true;
        } else {
            $stmt = database::getInstance()->con()->query("SELECT `version` FROM `".property::getInstance()->get('db_prefix')."_version` LIMIT 1");
            $res = $stmt->fetch(\PDO::FETCH_ASSOC);
            $usedVersion = $res['version'];
            if($usedVersion == version) {
                $params['notify']['actual_version'] = true;
            }
            $updateQuery = null;
            if(sizeof($params['notify']) == 0) {
                if(system::getInstance()->post('startupdate')) {
                    $update_sql_array_files = $this->foundVersionUpdates($usedVersion, version);
                    if(is_array($update_sql_array_files)) {
                        foreach($update_sql_array_files as $update_file) {
                            $updateQuery .= @file_get_contents(root . '/install/sql/' . $update_file) . '\n';
                        }
                    }
                    if($updateQuery != null) {
                        database::getInstance()->con()->exec($updateQuery);
                        @file_put_contents(root . "/install/.update-".version, 'locked'); // only 1 run
                        $params['notify']['success'] = true;
                    } else {
                        $params['notify']['nosql_data'] = true;
                    }
                }
            }
        }
        return template::getInstance()->twigRender('update.tpl', $params);
    }

    /**
     * Function for compare versions and found sql updates for FFCMS. Drugs and india code inside (:
     * @param $old_version
     * @param $new_version
     * @return array|bool
     */
    public function foundVersionUpdates($old_version, $new_version) {
        if(!file_exists(root . '/install/sql/'))
            return false;
        if(system::getInstance()->ffVersionToCompare($old_version) >= system::getInstance()->ffVersionToCompare($new_version))
            return false;
        $scandir = scandir(root . '/install/sql/');
        $available_files = array();
        foreach($scandir as $item_file) {
            if(system::getInstance()->prefixEquals($item_file, 'update') && system::getInstance()->suffixEquals($item_file, '.sql'))
                $available_files[] = $item_file;
        }
        $found_update = array();
        $old_array = system::getInstance()->altexplode('.', $old_version);
        $new_array = system::getInstance()->altexplode('.', $new_version);
        $major_start = $old_array[1];
        $minor_start = $old_array[2];
        for($api=$old_array[0];$api<=$new_array[0];$api++) {
            for($major=$major_start;$major<=10;$major++) {
                if($api == $new_array[0] && $major > $new_array[1])
                    break;
                for($minor=$minor_start;$minor<=10;$minor++) {
                    $next_minor = $minor + 1;
                    $file = 'update-'.$api.'.'.$major.'.'.$minor.'-to-'.$api.'.'.$major.'.'.$next_minor.'.sql';
                    if(in_array($file, $available_files)) {
                        $found_update[] = $file;
                    } else { // not founded minor release, but m.b next major release?
                        $next_major = $major + 1;
                        $file = 'update-'.$api.'.'.$major.'.'.$minor.'-to-'.$api.'.'.$next_major.'.0.sql';
                        if(in_array($file, $available_files)) {
                            $found_update[] = $file;
                        } else { // not founder major release, mb api release?
                            $next_api = $api + 1;
                            $file = 'update-'.$api.'.'.$major.'.'.$minor.'-to-'.$next_api.'.0.0.sql';
                            if(in_array($file, $available_files)) {
                                $found_update[] = $file;
                            }
                        }
                    }
                }
            }
            $major_start = 0;
            $minor_start = 0;
        }
        return $found_update;
    }

    private function viewChangeLang() {
        $to = system::getInstance()->get('to');
        if(language::getInstance()->canUse($to)) {
            setcookie('ffcms_lang', $to, null, '/');
            return language::getInstance()->get('install_langchange_success');
        }
        return language::getInstance()->get('install_langchange_fail');
    }

    private function viewInstall() {
        $params = array();

        if(file_exists(root.'/install/.lock')) {
            $params['notify']['prepare']['lock'] = true;
        }
        if(file_exists(root . '/config.php') && !is_writable(root . '/config.php')) {
            $params['notify']['prepare']['cfg_write'] = true;
        }
        if(!is_writable(root . '/install/')) {
            $params['notify']['prepare']['inst_write'] = true;
        }
        if(!file_exists(root . '/install/sql/install.sql')) {
            $params['notify']['prepare']['sql_notfound'] = true;
        }
        if(!file_exists(root . '/install/.install')) {
            $params['notify']['prepare']['inst_unlock'] = true;
        }
        $timezone_array = array('Pacific/Kwajalein', 'Pacific/Samoa', 'US/Hawaii', 'US/Alaska', 'US/Pacific', 'US/Arizona', 'America/Mexico_City', 'S/East-Indiana', 'America/Santiago', 'America/Buenos_Aires', 'Brazil/DeNoronha', 'Atlantic/Cape_Verde', 'Europe/London', 'Europe/Berlin', 'Europe/Kiev', 'Europe/Moscow', 'Europe/Samara', 'Asia/Yekaterinburg', 'Asia/Novosibirsk', 'Asia/Krasnoyarsk', 'Asia/Irkutsk', 'Asia/Yakutsk', 'Asia/Vladivostok', 'Asia/Magadan', 'Asia/Kamchatka', 'Pacific/Tongatapu', 'Pacific/Kiritimati');
        template::getInstance()->set(template::TYPE_SYSTEM, 'timezones', $timezone_array);

        if(sizeof($params['notify']) == 0) {
            if(system::getInstance()->post('submit')) {
                $testCon = null;
                try {
                    $testCon = @new \PDO("mysql:host=".system::getInstance()->post('config:db_host').";dbname=".system::getInstance()->post('config:db_name')."", system::getInstance()->post('config:db_user'), system::getInstance()->post('config:db_pass'));
                } catch(\PDOException $exception) {
                    $params['notify']['process']['db_conn_miss'] = true;
                }
                if($testCon != null) {
                    $reg_login = system::getInstance()->post('admin:login');
                    $reg_email = system::getInstance()->post('admin:email');
                    $reg_pass = system::getInstance()->post('admin:pass');
                    $reg_repass = system::getInstance()->post('admin:repass');
                    if (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
                        $params['notify']['process']['reg_email_wrong'] = true;
                    }
                    if (!system::getInstance()->validPasswordLength($reg_pass)) {
                        $params['notify']['process']['reg_pass_wrong'] = true;
                    }
                    if(system::getInstance()->length($reg_login) < 3 || system::getInstance()->length($reg_login) > 64) {
                        $params['notify']['process']['reg_login_wrong'] = true;
                    }
                    if($reg_pass != $reg_repass) {
                        $params['notify']['process']['reg_repass_nomatch'] = true;
                    }

                    if(sizeof($params['notify']) == 0) {
                        $configs_data = '<?php'."\n";
                        foreach(system::getInstance()->post(null) as $var_name=>$var_value) {
                            if(system::getInstance()->prefixEquals($var_name, 'config:')) {
                                $var_name = substr($var_name, strlen('config:'));
                                $var_name = system::getInstance()->nohtml($var_name);
                                if($var_name === 'seo_title') {
                                    foreach(language::getInstance()->getAvailable() as $clang) {
                                        $configs_data .= '$config[\''.$var_name.'\'][\'' . $clang . '\'] = "' . system::getInstance()->nohtml($var_value[$clang]) . '";' . "\n";
                                    }
                                } else
                                    $configs_data .= '$config[\''.$var_name.'\'] = "' . system::getInstance()->nohtml($var_value) . '"'.";\n";
                            }
                        }
                        $random_password_salt = system::getInstance()->randomString(rand(12,16));
                        $configs_data .= '$config[\'tpl_dir\'] = "templates";
$config[\'tpl_name\'] = "default";
$config[\'debug\'] = true;
$config[\'multi_title\'] = false;
$config[\'cache_interval\'] = "120";
$config[\'token_time\'] = "86400";
$config[\'user_friendly_url\'] = false;
$config[\'mail_from\'] = "admin@example.com";
$config[\'mail_ownername\'] = "Site Admin";
$config[\'mail_smtp_use\'] = false;
$config[\'mail_smtp_host\'] = "smtp.yandex.ru";
$config[\'mail_smtp_port\'] = "25";
$config[\'mail_smtp_auth\'] = true;
$config[\'mail_smtp_login\'] = "admin@example.com";
$config[\'mail_smtp_password\'] = "madness";
$config[\'password_salt\'] = "'.$random_password_salt.'";
';
                        $configs_data .= '?>';
                        file_put_contents(root . '/install/.lock', 'Install success');
                        file_put_contents(root . '/config.php', $configs_data);
                        $prefix = system::getInstance()->post('config:db_prefix');
                        if(!system::getInstance()->isLatinOrNumeric($prefix)) {
                            $prefix = "ffcms";
                        }

                        $query_dump = str_replace('{$db_prefix}', $prefix, file_get_contents(root . '/install/sql/install.sql'));
                        $testCon->exec($query_dump);
                        $md5_doublehash = system::getInstance()->doublemd5($reg_pass, $random_password_salt);
                        $stmt = $testCon->prepare("INSERT INTO ".$prefix."_user (`login`, `email`, `nick`, `pass`, `access_level`) VALUES(?, ?, 'admin', ?, '3')");
                        $stmt->bindParam(1, $reg_login, \PDO::PARAM_STR);
                        $stmt->bindParam(2, $reg_email, \PDO::PARAM_STR);
                        $stmt->bindParam(3, $md5_doublehash, \PDO::PARAM_STR, 32);
                        $stmt->execute();
                        $user_id = $testCon->lastInsertId();
                        $stmt = null;
                        $stmt = $testCon->prepare("INSERT INTO ".$prefix."_user_custom(`id`) VALUES (?)");
                        $stmt->bindParam(1, $user_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        $stmt = null;
                        $testCon = null;
                        $params['notify']['success'] = true;
                    }
                }
                foreach(system::getInstance()->post(null) as $var_name=>$var_value) {
                    if(system::getInstance()->prefixEquals($var_name, 'config:')) {
                        $var_name = substr($var_name, strlen('config:'));
                        template::getInstance()->set('cfg', $var_name, $var_value);
                    }
                }
            }
        }

        return template::getInstance()->twigRender('install.tpl', $params);
    }

    private function viewMain() {
        return template::getInstance()->twigRender('switch.tpl', array());
    }

    public function isInstalled() {
        return file_exists(root . '/config.php');
    }
}